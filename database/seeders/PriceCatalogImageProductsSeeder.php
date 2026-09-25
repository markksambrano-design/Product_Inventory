<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class PriceCatalogImageProductsSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()
            ->where('image', 'like', 'products/catalog-%')
            ->orderBy('id')
            ->each(function (Product $product): void {
                $costPrice = 35 + (($product->id * 19) % 166);
                $sellingPrice = $costPrice + 15 + (($product->id * 11) % 61);

                $product->update([
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                ]);
            });
    }
}