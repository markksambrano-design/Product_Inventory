<?php

namespace App\Http\Controllers;

use App\Models\ExpiredProductDisposal;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Services\ActivityLogger;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpiredProductDisposalController extends Controller
{
    public function index()
    {
        $disposals = ExpiredProductDisposal::with(['productBatch.product', 'user'])
            ->latest()->paginate(15);

        return view('expired-disposals.index', compact('disposals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $batches = ProductBatch::with('product')
            ->where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', today())
            ->orderBy('expiration_date')
            ->get();

        return view('expired-disposals.create', compact('batches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, InventoryMovementService $movements)
    {
        $validated = $request->validate([
            'product_batch_id' => 'required|exists:product_batches,id',
            'quantity' => 'required|numeric|min:0.01',
            'disposal_date' => 'required|date',
            'remarks' => 'nullable|string',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        $movements->assertAvailable($validated['idempotency_key'] ?? null);

        $details = DB::transaction(function () use ($validated, $movements) {
            $batch = ProductBatch::lockForUpdate()->findOrFail($validated['product_batch_id']);

            if (!$batch->expiration_date || !$batch->expiration_date->isBefore(today())) {
                throw ValidationException::withMessages([
                    'product_batch_id' => 'Only expired batches can be disposed.',
                ]);
            }

            if ((float) $validated['quantity'] > (float) $batch->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Disposal quantity exceeds the remaining batch stock.',
                ]);
            }

            $product = Product::lockForUpdate()->findOrFail($batch->product_id);

            $disposal = ExpiredProductDisposal::create([
                'product_batch_id' => $batch->id,
                'user_id' => auth()->id(),
                'quantity' => $validated['quantity'],
                'reason' => 'Expired',
                'disposal_date' => $validated['disposal_date'],
                'remarks' => $validated['remarks'] ?? null,
                'idempotency_key' => $validated['idempotency_key'] ?? null,
            ]);

            $batch->decrement('quantity', $validated['quantity']);
            $product->decrement('quantity', $validated['quantity']);

            $movements->record([
                'product_id' => $product->id,
                'product_batch_id' => $batch->id,
                'movement_type' => 'expired_disposal',
                'quantity' => -$validated['quantity'],
                'source_type' => ExpiredProductDisposal::class,
                'source_id' => $disposal->id,
                'movement_date' => $validated['disposal_date'],
                'remarks' => $validated['remarks'] ?? null,
            ]);

            return [
                'product' => $product->product_name,
                'unit' => $product->unit,
                'batch' => $batch->batch_number ?: 'No Batch',
            ];
        });

        ActivityLogger::log(
            'Disposed',
            'Inventory',
            'Disposed ' . $validated['quantity'] . ' ' . $details['unit'] .
            ' of ' . $details['product'] . ' (Batch: ' . $details['batch'] . ')'
        );

        return redirect()->route('expired-disposals.index')
            ->with('success', 'Expired stock disposed successfully.');
    }
}
