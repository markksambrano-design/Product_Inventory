<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SalesClosing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sale::with('user')->latest('sold_at')->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create', ['products' => Product::where('status', true)->orderBy('product_name')->get()]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'product_id' => 'required|array|min:1', 'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|numeric|min:.01', 'unit_price.*' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0', 'cash_received' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,gcash', 'idempotency_key' => 'required|string|max:100|unique:sales,idempotency_key',
        ]);
        $subtotal = 0;
        foreach ($v['product_id'] as $i => $productId) $subtotal += $v['quantity'][$i] * $v['unit_price'][$i];
        $discount = (float) ($v['discount'] ?? 0);
        $total = max(0, $subtotal - $discount);
        if ((float) $v['cash_received'] < $total) throw ValidationException::withMessages(['cash_received' => 'Payment is less than the sale total.']);
        $sale = DB::transaction(function () use ($v, $subtotal, $discount, $total) {
            $sale = Sale::create(['sale_number' => 'SALE-'.now()->format('YmdHis').'-'.random_int(100, 999), 'user_id' => auth()->id(), 'status' => $discount > 0 && auth()->user()->role !== 'admin' ? 'pending_discount' : 'completed', 'idempotency_key' => $v['idempotency_key'], 'subtotal' => $subtotal, 'discount' => $discount, 'total' => $total, 'cash_received' => $v['cash_received'], 'change_due' => $v['cash_received'] - $total, 'payment_method' => $v['payment_method'], 'sold_at' => now()]);
            foreach ($v['product_id'] as $i => $productId) $sale->items()->create(['product_id' => $productId, 'quantity' => $v['quantity'][$i], 'unit_price' => $v['unit_price'][$i], 'line_total' => $v['quantity'][$i] * $v['unit_price'][$i]]);
            if ($sale->status === 'completed') $this->depleteSale($sale);
            return $sale;
        });
        return redirect()->route('sales.show', $sale)->with('success', $sale->status === 'pending_discount' ? 'Sale saved and waiting for discount approval.' : 'Sale completed.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'user', 'returns']);
        return view('sales.show', compact('sale'));
    }

    public function approveDiscount(Sale $sale)
    {
        abort_unless($sale->status === 'pending_discount', 422, 'Sale is not waiting for discount approval.');
        DB::transaction(function () use ($sale) { $sale->update(['status' => 'completed', 'discount_approved_by' => auth()->id()]); $this->depleteSale($sale->fresh()); });
        return back()->with('success', 'Discount approved and sale completed.');
    }

    public function returnSale(Request $request, Sale $sale)
    {
        $v = $request->validate(['sale_item_id' => 'required|exists:sale_items,id', 'quantity' => 'required|numeric|min:.01', 'reason' => 'required|string|min:5|max:255']);
        DB::transaction(function () use ($v, $sale) {
            $item = SaleItem::where('id', $v['sale_item_id'])->where('sale_id', $sale->id)->lockForUpdate()->firstOrFail();
            $returned = (float) $sale->returns()->where('sale_item_id', $item->id)->sum('quantity');
            if ($returned + (float) $v['quantity'] > (float) $item->quantity) throw ValidationException::withMessages(['quantity' => 'Return quantity exceeds sold quantity.']);
            $refund = (float) $v['quantity'] * (float) $item->unit_price;
            $return = SaleReturn::create(['sale_id' => $sale->id, 'sale_item_id' => $item->id, 'user_id' => auth()->id(), 'quantity' => $v['quantity'], 'refund_amount' => $refund, 'reason' => $v['reason']]);
            $product = Product::lockForUpdate()->findOrFail($item->product_id); $product->increment('quantity', $v['quantity']);
            $returnBatch = ProductBatch::create(['product_id' => $product->id, 'batch_number' => 'RETURN-'.$return->id, 'quantity' => $v['quantity'], 'received_date' => today()]);
            $return->update(['batch_id' => $returnBatch->id]);
            InventoryMovement::create(['product_id' => $product->id, 'product_batch_id' => $returnBatch->id, 'movement_type' => 'sale_return', 'quantity' => $v['quantity'], 'source_type' => SaleReturn::class, 'source_id' => $return->id, 'movement_date' => today(), 'remarks' => $v['reason']]);
        });
        return back()->with('success', 'Return recorded and refund calculated.');
    }

    public function closeDay(Request $request)
    {
        $v = $request->validate(['closing_date' => 'required|date', 'remarks' => 'nullable|string']);
        abort_if(SalesClosing::whereDate('closing_date', $v['closing_date'])->exists(), 422, 'This date is already closed.');
        $sales = Sale::where('status', 'completed')->whereDate('sold_at', $v['closing_date']);
        $rows = (clone $sales)->get();
        $closing = SalesClosing::create(['closing_date' => $v['closing_date'], 'closed_by' => auth()->id(), 'total_sales' => $rows->sum('total'), 'cash_sales' => $rows->where('payment_method', 'cash')->sum('total'), 'card_sales' => $rows->where('payment_method', 'card')->sum('total'), 'gcash_sales' => $rows->where('payment_method', 'gcash')->sum('total'), 'refunds' => SaleReturn::whereDate('created_at', $v['closing_date'])->sum('refund_amount'), 'remarks' => $v['remarks'] ?? null]);
        return back()->with('success', 'Daily sales closing completed.');
    }

    private function depleteSale(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            $product = Product::lockForUpdate()->findOrFail($item->product_id);
            if ((float) $product->quantity < (float) $item->quantity) throw ValidationException::withMessages(['quantity' => 'Insufficient stock for '.$product->product_name.'.']);
            $remaining = (float) $item->quantity;
            $batches = ProductBatch::where('product_id', $product->id)->where('quantity', '>', 0)->where(fn ($q) => $q->whereNull('expiration_date')->orWhereDate('expiration_date', '>=', today()))->orderByRaw('CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END')->orderBy('expiration_date')->lockForUpdate()->get();
            foreach ($batches as $batch) { if ($remaining <= 0) break; $take = min((float) $batch->quantity, $remaining); $batch->decrement('quantity', $take); $remaining -= $take; }
            if ($remaining > 0) throw ValidationException::withMessages(['quantity' => 'Insufficient usable batch stock.']);
            $product->decrement('quantity', $item->quantity);
            InventoryMovement::create(['product_id' => $product->id, 'movement_type' => 'sale', 'quantity' => -$item->quantity, 'source_type' => Sale::class, 'source_id' => $sale->id, 'movement_date' => today(), 'remarks' => 'POS sale '.$sale->sale_number]);
        }
    }
}