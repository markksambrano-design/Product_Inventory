<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrganizeCatalogProductsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        Product::query()
            ->where('image', 'like', 'products/catalog-%')
            ->each(function (Product $product) use ($categories): void {
                $product->update(['category_id' => $categories[$this->categoryFor($product->image)]]);
            });
    }

    private function categoryFor(string $image): string
    {
        $name = strtolower($image);

        return match (true) {
            $this->contains($name, ['lucky-me', 'payless']) => 'Instant Noodles',
            $this->contains($name, ['coca-cola', 'pepsi']) => 'Beverages',
            $this->contains($name, ['alaska', 'bear-brand', 'nestle-fresh-milk', 'nestle-low-fat-milk', 'nestle-yogurt']) => 'Dairy',
            $this->contains($name, ['nescafe', 'kopiko', 'great-taste', 'milo']) => 'Coffee & Powdered Drinks',
            $this->contains($name, ['datu-puti', 'silver-swan']) => 'Condiments & Sauces',
            $this->contains($name, ['century', 'mega', '555']) => 'Seafood',
            $this->contains($name, ['argentina', 'spam', 'purefoods']) => 'Meat & Poultry',
            $this->contains($name, ['oishi', 'jack-', 'rebisco', 'skyflakes', 'regent', 'granny', 'pringles', 'oreo', 'lays', 'leslies', 'nova', 'piattos']) => 'Snacks',
            default => 'Canned Goods',
        };
    }

    private function contains(string $value, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }
}