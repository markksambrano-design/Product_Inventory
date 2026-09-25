<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductLocationStock;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function product(float $quantity = 0): Product
    {
        $category = Category::create(['name' => 'Test Category', 'status' => true]);
        return Product::create([
            'category_id' => $category->id, 'product_code' => 'TEST-001',
            'product_name' => 'Test Product', 'unit' => 'Pack',
            'cost_price' => 10, 'selling_price' => 15, 'quantity' => $quantity,
            'minimum_stock' => 5, 'status' => true,
        ]);
    }

    public function test_staff_cannot_access_admin_pages(): void
    {
        $this->actingAs($this->user('staff'))->get('/users')->assertForbidden();
        $this->actingAs($this->user('staff'))->get('/reports')->assertForbidden();
        $this->actingAs($this->user('staff'))->get('/categories')->assertForbidden();
    }

    public function test_stock_out_uses_fefo_and_ignores_expired_batches(): void
    {
        $product = $this->product(60);
        $expired = ProductBatch::create(['product_id' => $product->id, 'batch_number' => 'OLD', 'quantity' => 10, 'expiration_date' => today()->subDay(), 'received_date' => today()->subDays(5)]);
        $first = ProductBatch::create(['product_id' => $product->id, 'batch_number' => 'FIRST', 'quantity' => 20, 'expiration_date' => today()->addDays(5), 'received_date' => today()]);
        $second = ProductBatch::create(['product_id' => $product->id, 'batch_number' => 'SECOND', 'quantity' => 30, 'expiration_date' => today()->addDays(10), 'received_date' => today()]);

        $this->actingAs($this->user('staff'))->post(route('stock-out.store'), [
            'product_id' => $product->id, 'quantity' => 25, 'reason' => 'Sold',
            'stock_out_date' => today()->toDateString(),
        ])->assertRedirect(route('stock-out.index'));

        $this->assertEquals(10, $expired->fresh()->quantity);
        $this->assertEquals(0, $first->fresh()->quantity);
        $this->assertEquals(25, $second->fresh()->quantity);
        $this->assertEquals(35, $product->fresh()->quantity);
    }

    public function test_stock_out_rejects_quantity_above_non_expired_stock(): void
    {
        $product = $this->product(20);
        ProductBatch::create(['product_id' => $product->id, 'quantity' => 15, 'expiration_date' => today()->subDay()]);
        ProductBatch::create(['product_id' => $product->id, 'quantity' => 5, 'expiration_date' => today()->addDay()]);

        $this->actingAs($this->user('staff'))->post(route('stock-out.store'), [
            'product_id' => $product->id, 'quantity' => 6, 'stock_out_date' => today()->toDateString(),
        ])->assertSessionHasErrors('quantity');
    }

    public function test_physical_count_updates_batch_and_product_equally(): void
    {
        $product = $this->product(10);
        $batch = ProductBatch::create(['product_id' => $product->id, 'quantity' => 10, 'received_date' => today()]);

        $this->actingAs($this->user('staff'))->post(route('inventory-counts.store'), [
            'product_batch_id' => $batch->id, 'actual_quantity' => 7,
            'count_date' => today()->toDateString(), 'remarks' => 'Counted twice',
        ])->assertRedirect(route('inventory-counts.index'));

        $this->assertEquals(7, $batch->fresh()->quantity);
        $this->assertEquals(7, $product->fresh()->quantity);
        $this->assertDatabaseHas('stock_adjustments', ['product_batch_id' => $batch->id, 'type' => 'decrease', 'quantity' => 3]);
    }

    public function test_receiving_a_purchase_order_creates_stock_and_updates_inventory(): void
    {
        $admin = $this->user('admin');
        $product = $this->product(3);
        $supplier = Supplier::create(['supplier_name' => 'Test Supplier', 'status' => true]);
        $order = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001', 'supplier_id' => $supplier->id,
            'created_by' => $admin->id, 'status' => 'ordered',
            'order_date' => today(), 'total_amount' => 100,
        ]);
        $order->items()->create([
            'product_id' => $product->id, 'quantity' => 10,
            'unit_cost' => 10, 'expiration_date' => today()->addMonth(),
        ]);

        $this->actingAs($admin)->get(route('purchase-orders.show', $order))->assertOk();
        $this->actingAs($admin)->post(route('purchase-orders.receive', $order))->assertRedirect();

        $this->assertEquals(13, $product->fresh()->quantity);
        $this->assertEquals('received', $order->fresh()->status);
        $this->assertDatabaseHas('stock_ins', ['reference_number' => 'PO-TEST-001', 'quantity' => 10]);
    }

    public function test_new_purchase_order_requires_admin_approval_before_receiving(): void
    {
        $staff = $this->user('staff');
        $supplier = Supplier::create(['supplier_name' => 'Approval Supplier', 'status' => true]);
        $product = $this->product();
        $order = PurchaseOrder::create([
            'po_number' => 'PO-APPROVAL-001', 'supplier_id' => $supplier->id,
            'created_by' => $staff->id, 'status' => 'draft', 'approval_status' => 'pending',
            'order_date' => today(), 'total_amount' => 100,
        ]);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 10]);

        $this->actingAs($staff)->post(route('purchase-orders.receive', $order))
            ->assertForbidden();
        $this->actingAs($staff)->post(route('purchase-orders.approve', $order))
            ->assertForbidden();
        $this->actingAs($this->user('admin'))->post(route('purchase-orders.receive', $order))
            ->assertSessionHasErrors('purchase_order');
    }

    public function test_purchase_order_cancellation_requires_reason(): void
    {
        $admin = $this->user('admin');
        $supplier = Supplier::create(['supplier_name' => 'Cancel Supplier', 'status' => true]);
        $order = PurchaseOrder::create([
            'po_number' => 'PO-CANCEL-001', 'supplier_id' => $supplier->id,
            'created_by' => $admin->id, 'status' => 'ordered', 'approval_status' => 'approved',
            'order_date' => today(), 'total_amount' => 100,
        ]);

        $this->actingAs($admin)->post(route('purchase-orders.cancel', $order))
            ->assertSessionHasErrors('cancellation_reason');
        $this->actingAs($admin)->post(route('purchase-orders.cancel', $order), ['cancellation_reason' => 'Supplier unavailable'])
            ->assertRedirect();
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals('Supplier unavailable', $order->fresh()->cancellation_reason);
    }

    public function test_dashboard_and_generated_product_image_render(): void
    {
        $product = $this->product(5);
        $admin = $this->user('admin');

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Inventory Cost');
        $this->actingAs($admin)->get(route('products.placeholder', $product))
            ->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_stock_in_records_ledger_and_rejects_duplicate_key(): void
    {
        $product = $this->product();
        $staff = $this->user('staff');
        $payload = [
            'product_id' => $product->id,
            'quantity' => 5,
            'cost_price' => 0,
            'stock_in_date' => today()->toDateString(),
            'idempotency_key' => 'stock-in-test-key',
        ];

        $this->actingAs($staff)->post(route('stock-in.store'), $payload)->assertRedirect();
        $this->actingAs($staff)->post(route('stock-in.store'), $payload)->assertSessionHasErrors('idempotency_key');

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'movement_type' => 'stock_in',
            'quantity' => 5,
            'idempotency_key' => 'stock-in-test-key',
        ]);
        $this->assertEquals(5, $product->fresh()->quantity);
    }

    public function test_stock_transfer_moves_location_balance_without_changing_total_product_stock(): void
    {
        $product = $this->product(10);
        $admin = $this->user('admin');
        $source = Location::where('code', 'MAIN')->firstOrFail();
        $destination = Location::create(['code' => 'BRANCH-1', 'name' => 'Branch 1', 'status' => true]);
        ProductLocationStock::where(['product_id' => $product->id, 'location_id' => $source->id])->update(['quantity' => 10]);

        $this->actingAs($admin)->post(route('stock-transfers.store'), [
            'source_location_id' => $source->id,
            'destination_location_id' => $destination->id,
            'product_id' => [$product->id],
            'quantity' => [4],
            'transfer_date' => today()->toDateString(),
        ])->assertRedirect()->assertSessionDoesntHaveErrors();

        $this->assertEquals(6, ProductLocationStock::where(['product_id' => $product->id, 'location_id' => $source->id])->value('quantity'));
        $this->assertEquals(4, ProductLocationStock::where(['product_id' => $product->id, 'location_id' => $destination->id])->value('quantity'));
        $this->assertEquals(10, $product->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', ['product_id' => $product->id, 'movement_type' => 'transfer_out', 'quantity' => -4]);
    }

    public function test_customer_return_restock_increases_batch_and_ledger(): void
    {
        $product = $this->product(5);
        $batch = ProductBatch::create(['product_id' => $product->id, 'quantity' => 5, 'received_date' => today()]);
        $admin = $this->user('admin');

        $this->actingAs($admin)->post(route('stock-returns.store'), [
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'type' => 'customer_return',
            'disposition' => 'restock',
            'quantity' => 2,
            'return_date' => today()->toDateString(),
            'reason' => 'Customer returned unopened item',
        ])->assertRedirect(route('stock-returns.index'));

        $this->assertEquals(7, $product->fresh()->quantity);
        $this->assertEquals(7, $batch->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', ['product_id' => $product->id, 'movement_type' => 'customer_return', 'quantity' => 2]);
    }
}
