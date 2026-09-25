<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductBatch;
use Carbon\Carbon;
use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();

        $totalStock = Product::sum('quantity');

        $lowStock = Product::whereColumn('quantity', '<=', 'minimum_stock')
            ->where('quantity', '>', 0)
            ->count();

        $outOfStock = Product::where('quantity', '<=', 0)->count();

        $expiringSoon = ProductBatch::where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '>=', Carbon::today())
            ->whereDate('expiration_date', '<=', Carbon::today()->addDays(30))
            ->count();

        $expiredProducts = ProductBatch::where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', Carbon::today())
            ->count();

        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        $recentProducts = Product::with('category')
            ->latest()
            ->take(5)
            ->get();

        $lowStockProducts = Product::with('category')
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->orderBy('quantity')
            ->take(5)
            ->get();

        $inventoryCostValue = Product::sum(DB::raw('quantity * cost_price'));
        $inventoryRetailValue = Product::sum(DB::raw('quantity * selling_price'));
        $estimatedProfit = $inventoryRetailValue - $inventoryCostValue;
        $trend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = today()->subDays($daysAgo);
            return [
                'label' => $date->format('D'),
                'in' => (float) StockIn::whereDate('stock_in_date', $date)->sum('quantity'),
                'out' => (float) StockOut::whereDate('stock_out_date', $date)->sum('quantity'),
            ];
        });

        return view('dashboard', compact(
            'totalProducts',
            'totalStock',
            'lowStock',
            'outOfStock',
            'expiringSoon',
            'expiredProducts',
            'categories',
            'recentProducts',
            'lowStockProducts','inventoryCostValue','inventoryRetailValue','estimatedProfit','trend'
        ));
    }
}
