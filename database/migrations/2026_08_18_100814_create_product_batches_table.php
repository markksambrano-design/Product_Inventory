<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('stock_in_id')
                ->nullable()
                ->constrained('stock_ins')
                ->nullOnDelete();

            $table->string('batch_number')->nullable();

            $table->decimal('quantity', 10, 2)->default(0);

            $table->date('expiration_date')->nullable();

            $table->date('received_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
