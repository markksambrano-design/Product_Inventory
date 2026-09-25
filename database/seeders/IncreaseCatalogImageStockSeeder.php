<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockIn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncreaseCatalogImageStockSeeder extends Seeder
{
    private const TARGET_QUANTITY = 100;

    public function run(): void
    {
        Product::query()
            ->where('image', 'like', 'products/catalog-%')
            ->orderBy('id')
            ->each(function (Product $product): void {
                DB::transaction(function () use ($product): void {
                    $product->refresh();
                    $additionalQuantity = max(0, self::TARGET_QUANTITY - (float) $product->quantity);

                    if ($additionalQuantity === 0.0) {
                        return;
                    }

                    $reference = 'CATIMG-STOCK-'.$product->id;
                    $stockIn = StockIn::where('reference_number', $reference)->firstOrFail();
                    $batch = ProductBatch::where('stock_in_id', $stockIn->id)->firstOrFail();

                    $stockIn->increment('quantity', $additionalQuantity);
                    $batch->increment('quantity', $additionalQuantity);
                    $product->increment('quantity', $additionalQuantity);
                });
            });
    }
}