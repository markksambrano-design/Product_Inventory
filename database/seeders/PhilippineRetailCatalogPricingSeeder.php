<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class PhilippineRetailCatalogPricingSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()
            ->where('image', 'like', 'products/catalog-%')
            ->each(function (Product $product): void {
                $price = $this->retailPrice($product->image);

                $product->update([
                    'selling_price' => $price,
                    'cost_price' => round($price * 0.78, 2),
                ]);
            });
    }

    private function retailPrice(string $image): float
    {
        $name = strtolower(pathinfo($image, PATHINFO_FILENAME));

        $exact = [
            'coca-cola-1-5l' => 66.00, 'coca-cola-2l' => 80.00,
            'pepsi-1-5l' => 64.25, 'pepsi-2l' => 78.00,
            'nestle-all-purpose-cream' => 69.75,
            'century-tuna-hot-and-spicy' => 38.00,
            'century-tuna-flakes-in-oil' => 43.00,
            'century-tuna-flakes-in-vegetable-oil' => 49.25,
            'century-tuna-lite' => 44.00,
            'alaska-choco-milk-rtd' => 13.00,
            'alaska-classic-evaporated-filled-milk' => 59.25,
            'purefoods-chicken-breast-nuggets' => 490.00,
            'purefoods-tender-juicy-hotdog' => 198.00,
            'spam-less-sodium' => 224.25,
            'oreo-wafer-roll-vanilla' => 39.95,
            'oreo-wafer-roll-chocolate' => 39.95,
        ];

        foreach ($exact as $needle => $price) {
            if (str_contains($name, $needle)) {
                return $price;
            }
        }

        return match (true) {
            str_contains($name, 'coca-cola'), str_contains($name, 'pepsi') => str_contains($name, 'can') ? 35.00 : 60.00,
            str_contains($name, 'lucky-me'), str_contains($name, 'payless') => str_contains($name, 'mami') ? 18.00 : 15.00,
            str_contains($name, '555-') => 30.00,
            str_contains($name, 'mega-') => str_contains($name, 'tuna') ? 39.00 : 28.00,
            str_contains($name, 'century-') => 42.00,
            str_contains($name, 'argentina-') => str_contains($name, 'corned') ? 68.00 : 55.00,
            str_contains($name, 'purefoods-') => str_contains($name, 'bacon') ? 359.00 : (str_contains($name, 'hotdog') ? 198.00 : 105.00),
            str_contains($name, 'spam-') => 225.00,
            str_contains($name, 'alaska-') => str_contains($name, 'powdered') ? 185.00 : (str_contains($name, 'fresh') ? 92.00 : 48.00),
            str_contains($name, 'bear-brand') => str_contains($name, 'sachet') ? 12.00 : (str_contains($name, 'pouch') ? 180.00 : 35.00),
            str_contains($name, 'milo-') => str_contains($name, 'pouch') ? 165.00 : (str_contains($name, 'rtd') ? 35.00 : 15.00),
            str_contains($name, 'nescafe-') => str_contains($name, 'gold') ? 245.00 : (str_contains($name, 'rtd') ? 45.00 : 15.00),
            str_contains($name, 'kopiko-'), str_contains($name, 'great-taste-') => str_contains($name, 'candy') ? 8.00 : 15.00,
            str_contains($name, 'datu-puti-'), str_contains($name, 'silver-swan-') => 48.00,
            str_contains($name, 'oishi-'), str_contains($name, 'jack-'), str_contains($name, 'rebisco-'), str_contains($name, 'skyflakes-'), str_contains($name, 'regent-'), str_contains($name, 'granny-') => 22.00,
            str_contains($name, 'pringles-') => 115.00,
            str_contains($name, 'oreo-') => 35.00,
            str_contains($name, 'lays-') => 110.00,
            default => 45.00,
        };
    }
}