<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CATEGORIES = [
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
        $now = now();

        foreach (self::CATEGORIES as $name) {
            DB::table('categories')->updateOrInsert(
                ['name' => $name],
                [
                    'parent_id' => null,
                    'description' => "Products under {$name}",
                    'status' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        DB::table('products')
            ->whereNotNull('subcategory_id')
            ->orderBy('id')
            ->get(['id', 'subcategory_id'])
            ->each(function ($product): void {
                DB::table('products')->where('id', $product->id)->update([
                    'category_id' => $product->subcategory_id,
                ]);
            });

        $othersId = DB::table('categories')->where('name', 'Others')->value('id');
        $groceryId = DB::table('categories')->where('name', 'Grocery')->value('id');
        if ($groceryId) {
            DB::table('products')->where('category_id', $groceryId)->update(['category_id' => $othersId]);
        }

        $meatAndPoultryId = DB::table('categories')->where('name', 'Meat & Poultry')->value('id');
        $legacyMeatId = DB::table('categories')->where('name', 'Meat')->value('id');
        if ($legacyMeatId) {
            DB::table('products')->where('category_id', $legacyMeatId)->update(['category_id' => $meatAndPoultryId]);
        }

        DB::table('categories')->whereNotNull('parent_id')->update(['parent_id' => null]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategory_id');
        });

        if ($groceryId) {
            DB::table('categories')->where('id', $groceryId)->delete();
        }
        if ($legacyMeatId) {
            DB::table('categories')->where('id', $legacyMeatId)->delete();
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('categories')->nullOnDelete();
        });
    }
};
