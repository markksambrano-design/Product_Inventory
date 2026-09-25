<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockReturn;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockReturnController extends Controller
{
    public function index()
    {
        $returns = StockReturn::with(['product', 'productBatch'])->latest()->paginate(15);
        return view('stock-returns.index', compact('returns'));
    }

    public function create()
    {
        return view('stock-returns.create', [
            'products' => Product::where('status', true)->orderBy('product_name')->get(),
            'batches' => ProductBatch::with('product')->where('quantity', '>', 0)->orderBy('product_id')->get(),
        ]);
    }

    public function store(Request $request, InventoryMovementService $movements)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_batch_id' => 'nullable|exists:product_batches,id',
            'type' => 'required|in:customer_return,damaged',
            'disposition' => 'required|in:restock,quarantine,dispose',
            'quantity' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:100|unique:stock_returns,reference_number',
            'return_date' => 'required|date',
            'reason' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $movements) {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $batch = null;
            if ($validated['product_batch_id']) {
                $batch = ProductBatch::whereKey($validated['product_batch_id'])->where('product_id', $product->id)->lockForUpdate()->first();
                if (! $batch) {
                    throw ValidationException::withMessages(['product_batch_id' => 'The selected batch does not belong to this product.']);
                }
            }

            $positive = $validated['type'] === 'customer_return' && $validated['disposition'] === 'restock';
            if ($positive && ! $batch) {
                throw ValidationException::withMessages(['product_batch_id' => 'A batch is required when restocking a return.']);
            }
            if (! $positive && $validated['type'] === 'damaged') {
                $available = $batch ? (float) $batch->quantity : (float) $product->quantity;
                if ((float) $validated['quantity'] > $available) {
                    throw ValidationException::withMessages(['quantity' => 'Damage quantity exceeds available stock.']);
                }
                if ($batch) $batch->decrement('quantity', $validated['quantity']);
                $product->decrement('quantity', $validated['quantity']);
            } elseif ($positive) {
                $batch->increment('quantity', $validated['quantity']);
                $product->increment('quantity', $validated['quantity']);
            }

            $return = StockReturn::create($validated + [
                'user_id' => auth()->id(),
                'reference_number' => $validated['reference_number'] ?? 'RT-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
            ]);

            if ($positive || $validated['type'] === 'damaged') {
                $movements->record([
                    'product_id' => $product->id,
                    'product_batch_id' => $batch?->id,
                    'movement_type' => $positive ? 'customer_return' : 'damaged_goods',
                    'quantity' => $positive ? $validated['quantity'] : -$validated['quantity'],
                    'source_type' => StockReturn::class,
                    'source_id' => $return->id,
                    'movement_date' => $validated['return_date'],
                    'remarks' => $validated['remarks'] ?? null,
                ]);
            }
        });

        return redirect()->route('stock-returns.index')->with('success', 'Stock return/damage record saved.');
    }
}