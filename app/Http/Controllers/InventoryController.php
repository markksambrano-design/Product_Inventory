<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $type = $request->type;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $movements = collect();

        if (!$type || $type === 'stock_in') {
            $stockIns = StockIn::with(['product.category', 'supplier'])
                ->when($search, function ($query, $search) {
                    $query->whereHas('product', function ($q) use ($search) {
                        $q->where('product_name', 'like', "%{$search}%")
                            ->orWhere('product_code', 'like', "%{$search}%");
                    });
                })
                ->when($dateFrom, function ($query, $dateFrom) {
                    $query->whereDate('stock_in_date', '>=', $dateFrom);
                })
                ->when($dateTo, function ($query, $dateTo) {
                    $query->whereDate('stock_in_date', '<=', $dateTo);
                })
                ->get()
                ->map(function ($stockIn) {
                    return [
                        'id' => $stockIn->id,
                        'type' => 'Stock In',
                        'type_key' => 'stock_in',
                        'date' => $stockIn->stock_in_date,
                        'product' => $stockIn->product,
                        'quantity' => $stockIn->quantity,
                        'supplier' => $stockIn->supplier?->supplier_name,
                        'reason' => null,
                        'reference_number' => $stockIn->reference_number,
                        'remarks' => $stockIn->remarks,
                        'created_at' => $stockIn->created_at,
                    ];
                });

            $movements = $movements->concat($stockIns);
        }

        if (!$type || $type === 'stock_out') {
            $stockOuts = StockOut::with('product.category')
                ->when($search, function ($query, $search) {
                    $query->whereHas('product', function ($q) use ($search) {
                        $q->where('product_name', 'like', "%{$search}%")
                            ->orWhere('product_code', 'like', "%{$search}%");
                    });
                })
                ->when($dateFrom, function ($query, $dateFrom) {
                    $query->whereDate('stock_out_date', '>=', $dateFrom);
                })
                ->when($dateTo, function ($query, $dateTo) {
                    $query->whereDate('stock_out_date', '<=', $dateTo);
                })
                ->get()
                ->map(function ($stockOut) {
                    return [
                        'id' => $stockOut->id,
                        'type' => 'Stock Out',
                        'type_key' => 'stock_out',
                        'date' => $stockOut->stock_out_date,
                        'product' => $stockOut->product,
                        'quantity' => $stockOut->quantity,
                        'supplier' => null,
                        'reason' => $stockOut->reason,
                        'reference_number' => $stockOut->reference_number,
                        'remarks' => $stockOut->remarks,
                        'created_at' => $stockOut->created_at,
                    ];
                });

            $movements = $movements->concat($stockOuts);
        }

        $movements = $movements
            ->sortByDesc(function ($movement) {
                return $movement['date'] . ' ' . $movement['created_at'];
            })
            ->values();

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $currentItems = $movements
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $movements = new LengthAwarePaginator(
            $currentItems,
            $movements->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('inventory.index', compact(
            'movements',
            'search',
            'type',
            'dateFrom',
            'dateTo'
        ));
    }
}
