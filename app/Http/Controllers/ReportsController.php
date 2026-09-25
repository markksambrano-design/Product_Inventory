<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\InventoryMovement;
use App\Models\PurchaseOrder;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $type = $request->validate(['type' => 'required|in:products,stock_in,stock_out,expired,movements,valuation,profit,suppliers'])['type'];
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        return response()->streamDownload(function () use ($type, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            if ($type === 'products') {
                fputcsv($file, ['Code', 'Product', 'Category', 'Unit', 'Quantity', 'Minimum Stock', 'Cost Price', 'Selling Price', 'Status']);
                Product::with('category')->orderBy('product_name')->each(fn ($p) => fputcsv($file, [$p->product_code, $p->product_name, $p->category?->name, $p->unit, $p->quantity, $p->minimum_stock, $p->cost_price, $p->selling_price, $p->status ? 'Active' : 'Inactive']));
            } elseif ($type === 'stock_in') {
                fputcsv($file, ['Date', 'Product', 'Supplier', 'Batch', 'Quantity', 'Cost Price', 'Reference']);
                StockIn::with(['product', 'supplier'])->when($dateFrom, fn ($q) => $q->whereDate('stock_in_date', '>=', $dateFrom))->when($dateTo, fn ($q) => $q->whereDate('stock_in_date', '<=', $dateTo))->orderBy('stock_in_date')->each(fn ($s) => fputcsv($file, [$s->stock_in_date?->toDateString(), $s->product?->product_name, $s->supplier?->supplier_name, $s->batch_number, $s->quantity, $s->cost_price, $s->reference_number]));
            } elseif ($type === 'stock_out') {
                fputcsv($file, ['Date', 'Product', 'Quantity', 'Reason', 'Reference', 'Remarks']);
                StockOut::with('product')->when($dateFrom, fn ($q) => $q->whereDate('stock_out_date', '>=', $dateFrom))->when($dateTo, fn ($q) => $q->whereDate('stock_out_date', '<=', $dateTo))->orderBy('stock_out_date')->each(fn ($s) => fputcsv($file, [$s->stock_out_date?->toDateString(), $s->product?->product_name, $s->quantity, $s->reason, $s->reference_number, $s->remarks]));
            } elseif ($type === 'movements') {
                fputcsv($file, ['Date', 'Product', 'Type', 'Quantity', 'Source', 'Remarks']);
                InventoryMovement::with('product')->when($dateFrom, fn ($q) => $q->whereDate('movement_date', '>=', $dateFrom))->when($dateTo, fn ($q) => $q->whereDate('movement_date', '<=', $dateTo))->orderBy('movement_date')->each(fn ($m) => fputcsv($file, [$m->movement_date?->toDateString(), $m->product?->product_name, $m->movement_type, $m->quantity, class_basename($m->source_type), $m->remarks]));
            } elseif ($type === 'valuation') {
                fputcsv($file, ['Code', 'Product', 'Quantity', 'Cost Price', 'Inventory Value']);
                Product::orderBy('product_name')->each(fn ($p) => fputcsv($file, [$p->product_code, $p->product_name, $p->quantity, $p->cost_price, (float) $p->quantity * (float) $p->cost_price]));
            } elseif ($type === 'profit') {
                fputcsv($file, ['Product', 'Quantity Sold', 'Sales', 'Cost', 'Gross Profit', 'Margin']);
                SaleItem::with('product')->selectRaw('product_id, SUM(quantity) quantity_sold, SUM(line_total) sales')->groupBy('product_id')->each(function ($row) use ($file) { $cost = (float) $row->quantity_sold * (float) $row->product->cost_price; $sales = (float) $row->sales; fputcsv($file, [$row->product->product_name, $row->quantity_sold, $sales, $cost, $sales - $cost, $sales > 0 ? (($sales - $cost) / $sales) * 100 : 0]); });
            } elseif ($type === 'suppliers') {
                fputcsv($file, ['Supplier', 'Orders', 'Received Orders', 'Ordered Value']);
                PurchaseOrder::with('supplier')->selectRaw('supplier_id, COUNT(*) orders, SUM(total_amount) ordered_value')->groupBy('supplier_id')->each(fn ($row) => fputcsv($file, [$row->supplier->supplier_name, $row->orders, PurchaseOrder::where('supplier_id', $row->supplier_id)->where('status', 'received')->count(), $row->ordered_value]));
            } else {
                fputcsv($file, ['Product', 'Batch', 'Quantity', 'Unit', 'Expiration Date']);
                ProductBatch::with('product')->where('quantity', '>', 0)->whereDate('expiration_date', '<', today())->orderBy('expiration_date')->each(fn ($b) => fputcsv($file, [$b->product?->product_name, $b->batch_number, $b->quantity, $b->product?->unit, $b->expiration_date?->toDateString()]));
            }
            fclose($file);
        }, $type.'-report-'.today()->toDateString().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function index(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $totalProducts = Product::count();

        $totalStock = Product::sum('quantity');

        $lowStockCount = Product::whereColumn(
            'quantity',
            '<=',
            'minimum_stock'
        )
        ->where('quantity', '>', 0)
        ->count();

        $outOfStockCount = Product::where(
            'quantity',
            '<=',
            0
        )->count();

        $expiredCount = ProductBatch::where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate(
                'expiration_date',
                '<',
                Carbon::today()
            )
            ->count();

        $stockIns = StockIn::with([
                'product',
                'supplier'
            ])
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate(
                    'stock_in_date',
                    '>=',
                    $dateFrom
                );
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate(
                    'stock_in_date',
                    '<=',
                    $dateTo
                );
            })
            ->latest()
            ->take(10)
            ->get();

        $stockOuts = StockOut::with('product')
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate(
                    'stock_out_date',
                    '>=',
                    $dateFrom
                );
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate(
                    'stock_out_date',
                    '<=',
                    $dateTo
                );
            })
            ->latest()
            ->take(10)
            ->get();

        $lowStockProducts = Product::with('category')
            ->whereColumn(
                'quantity',
                '<=',
                'minimum_stock'
            )
            ->orderBy('quantity')
            ->take(10)
            ->get();

        $nearExpiryCount = ProductBatch::where('quantity', '>', 0)->whereNotNull('expiration_date')->whereBetween('expiration_date', [today(), today()->addDays(30)])->count();
        $inventoryValue = Product::selectRaw('SUM(quantity * cost_price) total')->value('total') ?? 0;
        $profitSummary = SaleItem::selectRaw('SUM(line_total) sales')->value('sales') ?? 0;
        $fastMovers = SaleItem::with('product')->selectRaw('product_id, SUM(quantity) sold')->groupBy('product_id')->orderByDesc('sold')->limit(10)->get();
        $slowMovers = SaleItem::with('product')->selectRaw('product_id, SUM(quantity) sold')->groupBy('product_id')->orderBy('sold')->limit(10)->get();

        return view('reports.index', compact(
            'totalProducts',
            'totalStock',
            'lowStockCount',
            'outOfStockCount',
            'expiredCount',
            'stockIns',
            'stockOuts',
            'lowStockProducts',
            'dateFrom',
            'dateTo', 'nearExpiryCount', 'inventoryValue', 'profitSummary', 'fastMovers', 'slowMovers'
        ));
    }
}
