<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockIn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RandomizeCatalogImageStockSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()
            ->where('image', 'like', 'products/catalog-%')
            ->orderBy('id')
            ->each(function (Product $product): void {
                DB::transaction(function () use ($product): void {
                    $product->refresh();
                    $targetQuantity = 101 + (($product->id * 37) % 150);
                    $difference = $targetQuantity - (float) $product->quantity;

                    if ($difference === 0.0) {
                        return;
                    }

                    $stockIn = StockIn::where('reference_number', 'CATIMG-STOCK-'.$product->id)->firstOrFail();
                    $batch = ProductBatch::where('stock_in_id', $stockIn->id)->firstOrFail();

                    $stockIn->increment('quantity', $difference);
                    $batch->increment('quantity', $difference);
                    $product->increment('quantity', $difference);
                });
            });
    }
}