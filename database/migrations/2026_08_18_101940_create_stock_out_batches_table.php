<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_out_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_out_id')
                ->constrained('stock_outs')
                ->cascadeOnDelete();

            $table->foreignId('product_batch_id')
                ->constrained('product_batches')
                ->restrictOnDelete();

            $table->decimal('quantity', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_out_batches');
    }
};
