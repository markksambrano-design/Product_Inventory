<?php

namespace App\Http\Controllers;

use App\Models\ProductBatch;
use Illuminate\Http\Request;

class BatchInventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $today = now()->startOfDay();

        $batches = ProductBatch::with('product.category')
            ->where('quantity', '>', 0)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'batch_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'product',
                        function ($productQuery) use ($search) {
                            $productQuery
                                ->where(
                                    'product_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'product_code',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                });
            })
            ->when($status, function ($query, $status) use ($today) {
                if ($status === 'expired') {
                    $query
                        ->whereNotNull('expiration_date')
                        ->whereDate(
                            'expiration_date',
                            '<',
                            $today
                        );
                }

                if ($status === 'expiring') {
                    $query
                        ->whereNotNull('expiration_date')
                        ->whereDate(
                            'expiration_date',
                            '>=',
                            $today
                        )
                        ->whereDate(
                            'expiration_date',
                            '<=',
                            $today->copy()->addDays(30)
                        );
                }

                if ($status === 'safe') {
                    $query
                        ->whereNotNull('expiration_date')
                        ->whereDate(
                            'expiration_date',
                            '>',
                            $today->copy()->addDays(30)
                        );
                }

                if ($status === 'no_expiration') {
                    $query->whereNull('expiration_date');
                }
            })
            ->orderByRaw(
                'CASE
                    WHEN expiration_date IS NULL THEN 1
                    ELSE 0
                END'
            )
            ->orderBy('expiration_date')
            ->paginate(15)
            ->withQueryString();

        $totalBatches = ProductBatch::where(
            'quantity',
            '>',
            0
        )->count();

        $expiredBatches = ProductBatch::where(
                'quantity',
                '>',
                0
            )
            ->whereNotNull('expiration_date')
            ->whereDate(
                'expiration_date',
                '<',
                $today
            )
            ->count();

        $expiringBatches = ProductBatch::where(
                'quantity',
                '>',
                0
            )
            ->whereNotNull('expiration_date')
            ->whereDate(
                'expiration_date',
                '>=',
                $today
            )
            ->whereDate(
                'expiration_date',
                '<=',
                $today->copy()->addDays(30)
            )
            ->count();

        $noExpirationBatches = ProductBatch::where(
                'quantity',
                '>',
                0
            )
            ->whereNull('expiration_date')
            ->count();

        return view(
            'batch-inventory.index',
            compact(
                'batches',
                'search',
                'status',
                'totalBatches',
                'expiredBatches',
                'expiringBatches',
                'noExpirationBatches'
            )
        );
    }
}
