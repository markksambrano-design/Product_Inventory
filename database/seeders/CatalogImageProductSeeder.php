<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogImageProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['name' => 'Catalog Products'],
            ['description' => 'Products generated from the local image catalog.', 'status' => true]
        );

        $images = collect(Storage::disk('public')->files('products'))
            ->filter(fn (string $path) => Str::startsWith(basename($path), 'catalog-'))
            ->filter(fn (string $path) => in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true))
            ->sort()
            ->values();

        foreach ($images as $index => $image) {
            [$name, $brand] = $this->productDetails($image);

            Product::updateOrCreate(
                ['image' => $image],
                [
                    'category_id' => $category->id,
                    'product_code' => 'CATIMG-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'product_name' => $name,
                    'brand' => $brand,
                    'unit' => 'pack',
                    'cost_price' => 0,
                    'selling_price' => 0,
                    'quantity' => 0,
                    'minimum_stock' => 5,
                    'status' => true,
                    'description' => 'Imported from the local product image catalog. Update its price and stock before use.',
                ]
            );
        }
    }

    private function productDetails(string $image): array
    {
        $slug = Str::of(pathinfo($image, PATHINFO_FILENAME))
            ->after('catalog-')
            ->replaceStart('new-', '')
            ->replace('-clean', '')
            ->replace('-', ' ')
            ->squish()
            ->toString();

        $brands = [
            'bear brand' => 'Bear Brand', 'coca cola' => 'Coca-Cola', 'century' => 'Century',
            'datu puti' => 'Datu Puti', 'great taste' => 'Great Taste', 'granny' => 'Granny Goose',
            'jack' => "Jack 'n Jill", 'kopiko' => 'Kopiko', 'lucky me' => 'Lucky Me!',
            'mega' => 'Mega', 'milo' => 'Milo', 'nescafe' => 'Nescafé', 'nestle' => 'Nestlé',
            'oishi' => 'Oishi', 'pepsi' => 'Pepsi', 'pringles' => 'Pringles', 'rebisco' => 'Rebisco',
            'silver swan' => 'Silver Swan', 'skyflakes' => 'SkyFlakes', 'spam' => 'Spam',
            'alaska' => 'Alaska', 'argentina' => 'Argentina', 'payless' => 'Payless', 'purefoods' => 'Purefoods',
        ];

        foreach ($brands as $prefix => $brand) {
            if (Str::startsWith($slug, $prefix.' ')) {
                return [Str::title(Str::after($slug, $prefix.' ')), $brand];
            }
        }

        return [Str::title($slug), null];
    }
}