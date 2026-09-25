<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductLocationStock;
use App\Models\StockTransfer;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockTransferController extends Controller
{
    public function index()
    {
        $transfers = StockTransfer::with(['items', 'sourceLocation', 'destinationLocation'])->latest()->paginate(15);
        return view('stock-transfers.index', compact('transfers'));
    }

    public function create()
    {
        return view('stock-transfers.create', [
            'locations' => Location::where('status', true)->orderBy('name')->get(),
            'products' => Product::where('status', true)->orderBy('product_name')->get(),
        ]);
    }

    public function store(Request $request, InventoryMovementService $movements)
    {
        $validated = $request->validate([
            'source_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id|different:source_location_id',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:100|unique:stock_transfers,reference_number',
            'transfer_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $transfer = DB::transaction(function () use ($validated, $movements) {
            $transfer = StockTransfer::create([
                'reference_number' => $validated['reference_number'] ?? 'TR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'source_location_id' => $validated['source_location_id'],
                'destination_location_id' => $validated['destination_location_id'],
                'user_id' => auth()->id(),
                'transfer_date' => $validated['transfer_date'],
                'remarks' => $validated['remarks'] ?? null,
            ]);

            foreach ($validated['product_id'] as $index => $productId) {
                $quantity = (float) $validated['quantity'][$index];
                $source = ProductLocationStock::where('product_id', $productId)
                    ->where('location_id', $validated['source_location_id'])
                    ->lockForUpdate()->first();

                if (! $source || (float) $source->quantity < $quantity) {
                    throw ValidationException::withMessages(['quantity' => 'Transfer quantity exceeds source location stock.']);
                }

                $source->decrement('quantity', $quantity);
                $destination = ProductLocationStock::firstOrCreate([
                    'product_id' => $productId,
                    'location_id' => $validated['destination_location_id'],
                ], ['quantity' => 0]);
                $destination->increment('quantity', $quantity);

                $item = $transfer->items()->create(['product_id' => $productId, 'quantity' => $quantity]);
                $product = Product::findOrFail($productId);
                $movements->record(['product_id' => $productId, 'location_id' => $validated['source_location_id'], 'movement_type' => 'transfer_out', 'quantity' => -$quantity, 'source_type' => StockTransfer::class, 'source_id' => $transfer->id, 'movement_date' => $validated['transfer_date'], 'remarks' => $transfer->reference_number]);
                $movements->record(['product_id' => $productId, 'location_id' => $validated['destination_location_id'], 'movement_type' => 'transfer_in', 'quantity' => $quantity, 'source_type' => StockTransfer::class, 'source_id' => $transfer->id, 'movement_date' => $validated['transfer_date'], 'remarks' => $transfer->reference_number]);
            }

            return $transfer;
        });

        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer completed: '.$transfer->reference_number);
    }
}