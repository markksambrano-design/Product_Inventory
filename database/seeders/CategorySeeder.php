<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Rice & Grains',
            'Canned Goods',
            'Snacks',
            'Biscuits & Crackers',
            'Beverages',
            'Coffee & Powdered Drinks',
            'Instant Noodles',
            'Condiments & Sauces',
            'Cooking Essentials',
            'Bakery',
            'Dairy',
            'Frozen Foods',
            'Meat & Poultry',
            'Seafood',
            'Fruits & Vegetables',
            'Personal Care',
            'Household Essentials',
            'Household/Cleaning',
            'Laundry Supplies',
            'Baby Products',
            'Pet Supplies',
            'Others',
        ];

        foreach ($categories as $name) {
            DB::table('categories')->updateOrInsert(
                ['name' => $name],
                [
                    'description' => "Products under {$name}",
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
