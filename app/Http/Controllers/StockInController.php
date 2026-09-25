<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockInController extends Controller
{
    public function index()
    {
        $stockIns = StockIn::with(['product', 'supplier'])
            ->latest()
            ->paginate(10);

        return view('stock-in.index', compact('stockIns'));
    }

    public function create()
    {
        $products = Product::where('status', true)
            ->orderBy('product_name')
            ->get();

        $suppliers = Supplier::where('status', true)
            ->orderBy('supplier_name')
            ->get();

        return view('stock-in.create', compact('products', 'suppliers'));
    }

    public function store(Request $request, InventoryMovementService $movements)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'required|numeric|min:0.01',
            'cost_price' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:255',
            'stock_in_date' => 'required|date|after_or_equal:' . today()->subDays(30)->toDateString() . '|before_or_equal:' . today()->toDateString(),
            'remarks' => 'nullable|string',
            'idempotency_key' => 'nullable|string|max:100',
        ]);

        $movements->assertAvailable($validated['idempotency_key'] ?? null);

        DB::transaction(function () use ($validated, $movements) {
            $stockIn = StockIn::create($validated);

            $product = Product::lockForUpdate()
                ->findOrFail($validated['product_id']);

            abort_unless($product->status, 422, 'Inactive products cannot receive stock.');

            $product->increment(
                'quantity',
                $validated['quantity']
            );

            if (array_key_exists('cost_price', $validated) && $validated['cost_price'] !== null) {
                $product->update([
                    'cost_price' => $validated['cost_price'],
                ]);
            }

            ProductBatch::create([
                'product_id' => $product->id,
                'stock_in_id' => $stockIn->id,
                'batch_number' => $validated['batch_number'] ?? null,
                'quantity' => $validated['quantity'],
                'expiration_date' => $validated['expiration_date'] ?? null,
                'received_date' => $validated['stock_in_date'],
            ]);

            $movements->record([
                'product_id' => $product->id,
                'product_batch_id' => ProductBatch::where('stock_in_id', $stockIn->id)->value('id'),
                'movement_type' => 'stock_in',
                'quantity' => $validated['quantity'],
                'source_type' => StockIn::class,
                'source_id' => $stockIn->id,
                'idempotency_key' => $validated['idempotency_key'] ?? null,
                'movement_date' => $validated['stock_in_date'],
                'remarks' => $validated['remarks'] ?? null,
            ]);
        });

        $product = Product::find($validated['product_id']);

        ActivityLogger::log(
            'Stock In',
            'Inventory',
            'Added ' .
            $validated['quantity'] . ' ' .
            $product->unit .
            ' of ' .
            $product->product_name
        );

        return redirect()
            ->route('stock-in.index')
            ->with('success', 'Stock added successfully.');
    }
}
