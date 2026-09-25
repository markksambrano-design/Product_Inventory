<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('product_code')->unique();
            $table->string('product_name');

            $table->string('brand')->nullable();

            $table->string('unit');

            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);

            $table->decimal('quantity', 10, 2)->default(0);

            $table->decimal('minimum_stock', 10, 2)->default(5);

            $table->date('expiration_date')->nullable();

            $table->string('barcode')->nullable()->unique();

            $table->string('image')->nullable();

            $table->boolean('status')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
