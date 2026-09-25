<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id(); $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft','ordered','partial','received','cancelled'])->default('draft');
            $table->date('order_date'); $table->date('expected_date')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 2); $table->decimal('received_quantity', 10, 2)->default(0);
            $table->decimal('unit_cost', 10, 2); $table->date('expiration_date')->nullable(); $table->timestamps();
        });
        Schema::create('sales', function (Blueprint $table) {
            $table->id(); $table->string('sale_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 12, 2); $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2); $table->decimal('cash_received', 12, 2)->default(0);
            $table->decimal('change_due', 12, 2)->default(0); $table->timestamp('sold_at'); $table->timestamps();
        });
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 2); $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 12, 2); $table->timestamps();
        });
        Schema::create('inventory_notifications', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type'); $table->string('title'); $table->text('message');
            $table->string('url')->nullable(); $table->timestamp('read_at')->nullable(); $table->timestamps();
        });
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email'); $table->boolean('successful'); $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable(); $table->timestamps();
        });
        Schema::create('backup_histories', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('filename'); $table->unsignedBigInteger('size')->default(0);
            $table->enum('type', ['manual','scheduled','restored'])->default('manual'); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_histories'); Schema::dropIfExists('login_histories');
        Schema::dropIfExists('inventory_notifications'); Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales'); Schema::dropIfExists('purchase_order_items'); Schema::dropIfExists('purchase_orders');
    }
};
