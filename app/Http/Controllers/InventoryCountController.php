<?php

namespace App\Http\Controllers;

use App\Models\InventoryCount;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockAdjustment;
use App\Services\ActivityLogger;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryCountController extends Controller
{
    public function index()
    {
        $counts = InventoryCount::with(['productBatch.product', 'user'])->latest()->paginate(15);
        return view('inventory-counts.index', compact('counts'));
    }

    public function create()
    {
        $batches = ProductBatch::with('product')->whereHas('product', fn ($q) => $q->where('status', true))
            ->orderBy('product_id')->orderBy('expiration_date')->get();
        return view('inventory-counts.create', compact('batches'));
    }

    public function store(Request $request, InventoryMovementService $movements)
    {
        $validated = $request->validate([
            'product_batch_id' => 'required|exists:product_batches,id',
            'actual_quantity' => 'required|numeric|min:0',
            'count_date' => 'required|date',
            'remarks' => 'nullable|string|max:1000',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        $movements->assertAvailable($validated['idempotency_key'] ?? null);

        [$productName, $batchNumber, $variance] = DB::transaction(function () use ($validated, $movements) {
            $batch = ProductBatch::lockForUpdate()->findOrFail($validated['product_batch_id']);
            $product = Product::lockForUpdate()->findOrFail($batch->product_id);
            $system = (float) $batch->quantity;
            $actual = (float) $validated['actual_quantity'];
            $variance = round($actual - $system, 2);

            $count = InventoryCount::create([
                'product_batch_id' => $batch->id, 'user_id' => auth()->id(),
                'system_quantity' => $system, 'actual_quantity' => $actual,
                'variance' => $variance, 'count_date' => $validated['count_date'],
                'remarks' => $validated['remarks'] ?? null,
            ]);

            if ($variance != 0.0) {
                StockAdjustment::create([
                    'product_id' => $product->id, 'product_batch_id' => $batch->id,
                    'type' => $variance > 0 ? 'increase' : 'decrease',
                    'quantity' => abs($variance), 'reason' => 'Physical Count Correction',
                    'adjustment_date' => $validated['count_date'], 'remarks' => $validated['remarks'] ?? null,
                ]);
                $batch->update(['quantity' => $actual]);
                $product->increment('quantity', $variance);

                $movements->record([
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'movement_type' => 'physical_count',
                    'quantity' => $variance,
                    'source_type' => InventoryCount::class,
                    'source_id' => $count->id,
                    'movement_date' => $validated['count_date'],
                    'remarks' => $validated['remarks'] ?? null,
                    'idempotency_key' => $validated['idempotency_key'] ?? null,
                ]);
            }

            return [$product->product_name, $batch->batch_number ?: 'No Batch', $variance];
        });

        ActivityLogger::log('Physical Count', 'Inventory', "Counted {$productName} batch {$batchNumber}; variance: {$variance}");
        return redirect()->route('inventory-counts.index')->with('success', 'Physical count saved and stock reconciled.');
    }
}
