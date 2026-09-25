<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryLedgerController extends Controller
{
    public function index(Request $request)
    {
        $movements = InventoryMovement::with(['product', 'productBatch', 'user'])
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->latest('movement_date')->latest('id')->paginate(25);

        $mismatches = Product::withSum('batches', 'quantity')->get()->filter(fn ($product) => abs((float) $product->quantity - (float) ($product->batches_sum_quantity ?? 0)) > 0.001);

        return view('inventory-ledger.index', [
            'movements' => $movements,
            'mismatches' => $mismatches,
            'products' => Product::orderBy('product_name')->get(['id', 'product_name']),
        ]);
    }
}