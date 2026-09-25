<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $category = Category::create(['name' => 'POS Test', 'status' => true]);
        $product = Product::create(['category_id' => $category->id, 'product_code' => 'POS-001', 'product_name' => 'POS Product', 'unit' => 'piece', 'cost_price' => 10, 'selling_price' => 20, 'quantity' => 10, 'minimum_stock' => 2, 'status' => true]);
        ProductBatch::create(['product_id' => $product->id, 'quantity' => 10, 'received_date' => today()]);
        return $product;
    }

    public function test_sale_depletes_stock_and_records_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product();
        $this->actingAs($admin)->post(route('sales.store'), ['product_id' => [$product->id], 'quantity' => [2], 'unit_price' => [20], 'cash_received' => 50, 'payment_method' => 'cash', 'idempotency_key' => 'sale-test-1'])->assertRedirect();
        $this->assertEquals(8, $product->fresh()->quantity);
        $this->assertDatabaseHas('sales', ['payment_method' => 'cash', 'total' => 40, 'status' => 'completed']);
    }

    public function test_staff_discount_waits_for_admin_approval(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = $this->product();
        $this->actingAs($staff)->post(route('sales.store'), ['product_id' => [$product->id], 'quantity' => [2], 'unit_price' => [20], 'discount' => 5, 'cash_received' => 35, 'payment_method' => 'cash', 'idempotency_key' => 'sale-test-2'])->assertRedirect();
        $sale = Sale::latest()->first();
        $this->assertEquals('pending_discount', $sale->status);
        $this->assertEquals(10, $product->fresh()->quantity);
        $this->actingAs($staff)->post(route('sales.approve-discount', $sale))->assertForbidden();
    }

    public function test_sale_return_restocks_a_new_batch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product();
        $this->actingAs($admin)->post(route('sales.store'), ['product_id' => [$product->id], 'quantity' => [2], 'unit_price' => [20], 'cash_received' => 40, 'payment_method' => 'cash', 'idempotency_key' => 'sale-test-3']);
        $sale = Sale::latest()->first();
        $this->actingAs($admin)->post(route('sales.return', $sale), ['sale_item_id' => $sale->items()->first()->id, 'quantity' => 1, 'reason' => 'Customer changed mind'])->assertRedirect();
        $this->assertEquals(9, $product->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', ['movement_type' => 'sale_return', 'quantity' => 1]);
    }
}