<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockIn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogImageStockSeeder extends Seeder
{
    private const INITIAL_QUANTITY = 10;

    public function run(): void
    {
        Product::query()
            ->where('image', 'like', 'products/catalog-%')
            ->orderBy('id')
            ->each(function (Product $product): void {
                DB::transaction(function () use ($product): void {
                    $reference = 'CATIMG-STOCK-'.$product->id;
                    $stockIn = StockIn::firstOrCreate(
                        ['reference_number' => $reference],
                        [
                            'product_id' => $product->id,
                            'quantity' => self::INITIAL_QUANTITY,
                            'cost_price' => 0,
                            'stock_in_date' => today(),
                            'remarks' => 'Initial stock for product imported from the local image catalog.',
                        ]
                    );

                    if (! $stockIn->wasRecentlyCreated) {
                        return;
                    }

                    ProductBatch::create([
                        'product_id' => $product->id,
                        'stock_in_id' => $stockIn->id,
                        'batch_number' => 'CATIMG-BATCH-'.$product->id,
                        'quantity' => self::INITIAL_QUANTITY,
                        'received_date' => today(),
                    ]);

                    $product->increment('quantity', self::INITIAL_QUANTITY);
                });
            });
    }
}