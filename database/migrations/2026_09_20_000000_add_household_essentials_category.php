<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('categories')->updateOrInsert(
            ['name' => 'Household Essentials'],
            [
                'description' => 'Dishwashing Liquid and other household essentials',
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('categories')->where('name', 'Household Essentials')->delete();
    }
};
