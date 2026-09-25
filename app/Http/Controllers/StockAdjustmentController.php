<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockAdjustment;
use App\Services\ActivityLogger;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with('product')
            ->latest()
            ->paginate(10);

        return view(
            'stock-adjustments.index',
            compact('adjustments')
        );
    }

    public function create()
    {
        $batches = ProductBatch::with('product')
            ->whereHas('product', fn ($query) => $query->where('status', true))
            ->orderBy('product_id')
            ->orderBy('expiration_date')
            ->get();

        return view(
            'stock-adjustments.create',
            compact('batches')
        );
    }

    public function store(Request $request, InventoryMovementService $movements)
    {
        $validated = $request->validate([
            'product_batch_id' => 'required|exists:product_batches,id',
            'type' => 'required|in:increase,decrease',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'adjustment_date' => 'required|date',
            'remarks' => 'nullable|string',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        $movements->assertAvailable($validated['idempotency_key'] ?? null);

        $validated['product_id'] = ProductBatch::findOrFail(
            $validated['product_batch_id']
        )->product_id;

        DB::transaction(function () use ($validated, $movements) {
            $product = Product::lockForUpdate()
                ->findOrFail($validated['product_id']);

            $batch = ProductBatch::whereKey($validated['product_batch_id'])
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if (!$batch) {
                throw ValidationException::withMessages([
                    'product_batch_id' => 'The selected batch does not belong to this product.',
                ]);
            }

            if (
                $validated['type'] === 'decrease' &&
                $validated['quantity'] > $batch->quantity
            ) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Adjustment quantity exceeds current stock. Current stock: '
                        . $batch->quantity . ' ' . $product->unit,
                ]);
            }

            $adjustment = StockAdjustment::create($validated);

            if ($validated['type'] === 'increase') {
                $batch->increment('quantity', $validated['quantity']);
                $product->increment(
                    'quantity',
                    $validated['quantity']
                );
            } else {
                $batch->decrement('quantity', $validated['quantity']);
                $product->decrement(
                    'quantity',
                    $validated['quantity']
                );
            }

            $movements->record([
                'product_id' => $product->id,
                'product_batch_id' => $batch->id,
                'movement_type' => 'stock_adjustment',
                'quantity' => $validated['type'] === 'increase' ? $validated['quantity'] : -$validated['quantity'],
                'source_type' => StockAdjustment::class,
                'source_id' => $adjustment->id,
                'movement_date' => $validated['adjustment_date'],
                'remarks' => $validated['remarks'] ?? null,
                'idempotency_key' => $validated['idempotency_key'] ?? null,
            ]);
        });

        $product = Product::find($validated['product_id']);

        ActivityLogger::log(
            'Stock Adjustment',
            'Inventory',
            ucfirst($validated['type']) .
            ' adjustment of ' .
            $validated['quantity'] . ' ' .
            $product->unit .
            ' for ' .
            $product->product_name .
            ' - Reason: ' .
            $validated['reason']
        );

        return redirect()
            ->route('stock-adjustments.index')
            ->with(
                'success',
                'Stock adjustment recorded successfully.'
            );
    }
}
