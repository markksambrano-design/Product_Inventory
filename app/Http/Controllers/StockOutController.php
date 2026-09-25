<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockOut;
use App\Models\StockOutBatch;
use App\Services\ActivityLogger;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockOutController extends Controller
{
    public function index()
    {
        $stockOuts = StockOut::with([
            'product',
            'batches.productBatch'
        ])
            ->latest()
            ->paginate(10);

        return view(
            'stock-out.index',
            compact('stockOuts')
        );
    }

    public function create()
    {
        $products = Product::where('status', true)
            ->orderBy('product_name')
            ->get();

        return view('stock-out.create', compact('products'));
    }

    public function store(Request $request, InventoryMovementService $movements)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'stock_out_date' => 'required|date|after_or_equal:' . today()->subDays(30)->toDateString() . '|before_or_equal:' . today()->toDateString(),
            'reference_number' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        $movements->assertAvailable($validated['idempotency_key'] ?? null);

        DB::transaction(function () use ($validated, $movements) {
            $product = Product::lockForUpdate()
                ->findOrFail($validated['product_id']);

            abort_unless($product->status, 422, 'Inactive products cannot be stocked out.');

            $availableBatchStock = ProductBatch::where(
                    'product_id',
                    $product->id
                )
                ->where('quantity', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('expiration_date')
                        ->orWhereDate(
                            'expiration_date',
                            '>=',
                            now()->toDateString()
                        );
                })
                ->sum('quantity');

            if ($validated['quantity'] > $availableBatchStock) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Not enough usable stock available. Available non-expired stock: '
                        . $availableBatchStock . ' ' . $product->unit,
                ]);
            }

            $stockOut = StockOut::create($validated);

            $remainingQuantity = (float) $validated['quantity'];

            /*
            |--------------------------------------------------------------------------
            | FEFO
            |--------------------------------------------------------------------------
            | Expiring batches first.
            | Batches without expiration are processed last.
            */

            $batches = ProductBatch::where(
                    'product_id',
                    $product->id
                )
                ->where('quantity', '>', 0)

                // Do not include expired batches.
                ->where(function ($query) {
                    $query->whereNull('expiration_date')
                        ->orWhereDate('expiration_date', '>=', now()->toDateString());
                })
                ->orderByRaw(
                    'CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END'
                )
                ->orderBy('expiration_date')
                ->orderBy('received_date')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $available = (float) $batch->quantity;

                $deductQuantity = min(
                    $available,
                    $remainingQuantity
                );

                $batch->decrement(
                    'quantity',
                    $deductQuantity
                );

                StockOutBatch::create([
                    'stock_out_id' => $stockOut->id,
                    'product_batch_id' => $batch->id,
                    'quantity' => $deductQuantity,
                ]);

                $remainingQuantity -= $deductQuantity;
            }

            /*
            |--------------------------------------------------------------------------
            | Safety check
            |--------------------------------------------------------------------------
            */

            if ($remainingQuantity > 0) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'There is not enough batch stock available.',
                ]);
            }

            $product->decrement(
                'quantity',
                $validated['quantity']
            );

            $movements->record([
                'product_id' => $product->id,
                'movement_type' => 'stock_out',
                'quantity' => -$validated['quantity'],
                'source_type' => StockOut::class,
                'source_id' => $stockOut->id,
                'idempotency_key' => $validated['idempotency_key'] ?? null,
                'movement_date' => $validated['stock_out_date'],
                'remarks' => $validated['remarks'] ?? null,
            ]);
        });

        $product = Product::find($validated['product_id']);

        ActivityLogger::log(
            'Stock Out',
            'Inventory',
            'Removed ' .
            $validated['quantity'] . ' ' .
            $product->unit .
            ' of ' .
            $product->product_name .
            ' - Reason: ' .
            ($validated['reason'] ?? 'N/A')
        );

        return redirect()
            ->route('stock-out.index')
            ->with(
                'success',
                'Stock removed successfully using FEFO.'
            );
    }
}
