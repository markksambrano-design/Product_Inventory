<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const GROCERY_SUBCATEGORIES = [
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
        'Household/Cleaning',
        'Laundry Supplies',
        'Baby Products',
        'Pet Supplies',
        'Others',
    ];

    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('subcategory_id')
                ->nullable()
                ->after('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        $now = now();
        $groceryId = DB::table('categories')->where('name', 'Grocery')->value('id');

        if (! $groceryId) {
            $groceryId = DB::table('categories')->insertGetId([
                'name' => 'Grocery',
                'description' => 'Food, drinks, household supplies, and daily essentials',
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (self::GROCERY_SUBCATEGORIES as $name) {
            $subcategoryId = DB::table('categories')->where('name', $name)->value('id');

            if ($subcategoryId) {
                DB::table('categories')->where('id', $subcategoryId)->update([
                    'parent_id' => $groceryId,
                    'status' => true,
                    'updated_at' => $now,
                ]);
            } else {
                $subcategoryId = DB::table('categories')->insertGetId([
                    'parent_id' => $groceryId,
                    'name' => $name,
                    'description' => "Grocery products under {$name}",
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('products')
                ->where('category_id', $subcategoryId)
                ->whereNull('subcategory_id')
                ->update([
                    'category_id' => $groceryId,
                    'subcategory_id' => $subcategoryId,
                ]);
        }
    }

    public function down(): void
    {
        DB::table('products')
            ->whereNotNull('subcategory_id')
            ->orderBy('id')
            ->get(['id', 'subcategory_id'])
            ->each(function ($product): void {
                DB::table('products')->where('id', $product->id)->update([
                    'category_id' => $product->subcategory_id,
                ]);
            });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategory_id');
        });

        DB::table('categories')->whereNotNull('parent_id')->update(['parent_id' => null]);

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
