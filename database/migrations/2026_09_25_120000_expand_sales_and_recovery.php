<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_method', 30)->default('cash')->after('cash_received');
            $table->string('status', 20)->default('completed')->after('sale_number');
            $table->foreignId('discount_approved_by')->nullable()->after('discount')->constrained('users')->nullOnDelete();
            $table->string('idempotency_key')->nullable()->unique()->after('sale_number');
        });

        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->decimal('refund_amount', 12, 2);
            $table->string('reason');
            $table->timestamps();
        });

        Schema::create('sales_closings', function (Blueprint $table) {
            $table->id();
            $table->date('closing_date')->unique();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('cash_sales', 12, 2)->default(0);
            $table->decimal('card_sales', 12, 2)->default(0);
            $table->decimal('gcash_sales', 12, 2)->default(0);
            $table->decimal('refunds', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::table('inventory_notifications', function (Blueprint $table) {
            $table->boolean('email_enabled')->default(false)->after('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_notifications', fn (Blueprint $table) => $table->dropColumn('email_enabled'));
        Schema::dropIfExists('sales_closings');
        Schema::dropIfExists('sale_returns');
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['discount_approved_by']);
            $table->dropUnique('sales_idempotency_key_unique');
            $table->dropColumn(['payment_method', 'status', 'discount_approved_by', 'idempotency_key']);
        });
    }
};