<?php

namespace App\Http\Controllers;

use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpirationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status;
        $search = $request->search;

        $today = Carbon::today();
        $sevenDays = Carbon::today()->addDays(7);
        $thirtyDays = Carbon::today()->addDays(30);

        $batches = ProductBatch::with('product.category')
            ->where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->when($search, function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where(function ($productQuery) use ($search) {
                        $productQuery->where('product_name', 'like', "%{$search}%")
                            ->orWhere('product_code', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    });
                });
            })
            ->when($status, function ($query, $status) use (
                $today,
                $sevenDays,
                $thirtyDays
            ) {
                if ($status === 'expired') {
                    $query->whereDate('expiration_date', '<', $today);
                }

                if ($status === '7_days') {
                    $query->whereDate('expiration_date', '>=', $today)
                        ->whereDate('expiration_date', '<=', $sevenDays);
                }

                if ($status === '30_days') {
                    $query->whereDate('expiration_date', '>', $sevenDays)
                        ->whereDate('expiration_date', '<=', $thirtyDays);
                }

                if ($status === 'safe') {
                    $query->whereDate('expiration_date', '>', $thirtyDays);
                }
            })
            ->orderBy('expiration_date')
            ->paginate(10)
            ->withQueryString();

        $expiredCount = ProductBatch::where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', $today)
            ->count();

        $sevenDayCount = ProductBatch::where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '>=', $today)
            ->whereDate('expiration_date', '<=', $sevenDays)
            ->count();

        $thirtyDayCount = ProductBatch::where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '>', $sevenDays)
            ->whereDate('expiration_date', '<=', $thirtyDays)
            ->count();

        $safeCount = ProductBatch::where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '>', $thirtyDays)
            ->count();

        return view('expiration.index', compact(
            'batches',
            'status',
            'search',
            'expiredCount',
            'sevenDayCount',
            'thirtyDayCount',
            'safeCount'
        ));
    }
}
