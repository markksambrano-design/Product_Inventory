<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Services\LocalProductImageMatcher;
use Illuminate\Database\Seeder;

class HouseholdEssentialsSeeder extends Seeder
{
    public function run(): void
    {
        $imageMatcher = app(LocalProductImageMatcher::class);
        $category = Category::firstOrCreate(
            ['name' => 'Household Essentials'],
            ['description' => 'Products under Household Essentials', 'status' => true]
        );

        $products = [
            ['Joy Lemon', 'Joy', 'bottle'],
            ['Joy Kalamansi', 'Joy', 'bottle'],
            ['Joy Ultra Lemon', 'Joy', 'bottle'],
            ['Joy Ultra Power', 'Joy', 'bottle'],
            ['Joy Antibacterial', 'Joy', 'bottle'],
            ['Joy Dishwashing Liquid', 'Joy', 'bottle'],
            ['Smart Dishwashing Liquid Lemon', 'Smart', 'bottle'],
            ['Smart Dishwashing Liquid Kalamansi', 'Smart', 'bottle'],
            ['Smart Antibacterial Dishwashing Liquid', 'Smart', 'bottle'],
            ['Axion Lemon', 'Axion', 'pack'],
            ['Axion Kalamansi', 'Axion', 'pack'],
            ['Axion Antibacterial', 'Axion', 'pack'],
            ['Axion Dishwashing Paste', 'Axion', 'pack'],
            ['Mr. Muscle Dishwashing Liquid Lemon', 'Mr. Muscle', 'bottle'],
            ['Mr. Muscle Dishwashing Liquid Antibacterial', 'Mr. Muscle', 'bottle'],
            ['Brite Dishwashing Liquid Lemon', 'Brite', 'bottle'],
            ['Brite Dishwashing Liquid Kalamansi', 'Brite', 'bottle'],
            ['Brite Dishwashing Liquid Antibacterial', 'Brite', 'bottle'],
            ['Nature Power Dishwashing Liquid Lemon', 'Nature Power', 'bottle'],
            ['Nature Power Dishwashing Liquid Kalamansi', 'Nature Power', 'bottle'],
            ['Nature Power Antibacterial Dishwashing Liquid', 'Nature Power', 'bottle'],
        ];

        foreach ($products as $index => [$name, $brand, $unit]) {
            $product = Product::updateOrCreate(
                ['product_code' => 'HE-DWL-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'category_id' => $category->id,
                    'product_name' => $name,
                    'brand' => $brand,
                    'unit' => $unit,
                    'cost_price' => 0,
                    'selling_price' => 0,
                    'quantity' => 0,
                    'minimum_stock' => 5,
                    'status' => true,
                    'description' => 'Household essential dishwashing product.',
                ]
            );

            $image = $imageMatcher->matchAndCopy($product);

            if ($image !== null && $product->image !== $image) {
                $product->update(['image' => $image]);
            } elseif ($image === null && $product->image !== null) {
                $product->update(['image' => null]);
            }
        }
    }
}