<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('product_location_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->unique(['product_id', 'location_id']);
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_batch_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('movement_type', 50);
            $table->decimal('quantity', 10, 2);
            $table->nullableMorphs('source');
            $table->string('idempotency_key')->nullable()->unique();
            $table->date('movement_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'movement_date']);
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('source_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('destination_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->date('transfer_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_batch_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->timestamps();
        });

        Schema::create('stock_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_batch_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['customer_return', 'damaged']);
            $table->enum('disposition', ['restock', 'quarantine', 'dispose'])->default('restock');
            $table->decimal('quantity', 10, 2);
            $table->string('reference_number')->nullable()->unique();
            $table->date('return_date');
            $table->string('reason');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::table('stock_ins', function (Blueprint $table) {
            $table->string('idempotency_key')->nullable()->unique();
        });
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->string('idempotency_key')->nullable()->unique();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'pgsql'])) {
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_quantity_nonnegative CHECK (quantity >= 0)');
            DB::statement('ALTER TABLE product_batches ADD CONSTRAINT product_batches_quantity_nonnegative CHECK (quantity >= 0)');
            DB::statement('ALTER TABLE product_location_stocks ADD CONSTRAINT product_location_stocks_quantity_nonnegative CHECK (quantity >= 0)');
            DB::statement('ALTER TABLE inventory_movements ADD CONSTRAINT inventory_movements_quantity_nonzero CHECK (quantity <> 0)');
            DB::statement('ALTER TABLE stock_transfer_items ADD CONSTRAINT stock_transfer_items_quantity_positive CHECK (quantity > 0)');
            DB::statement('ALTER TABLE stock_returns ADD CONSTRAINT stock_returns_quantity_positive CHECK (quantity > 0)');
        }

        DB::table('locations')->insert([
            'code' => 'MAIN',
            'name' => 'Main Warehouse',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $locationId = DB::table('locations')->where('code', 'MAIN')->value('id');
        DB::table('products')->select(['id', 'quantity'])->orderBy('id')->each(function ($product) use ($locationId) {
            DB::table('product_location_stocks')->insert([
                'product_id' => $product->id,
                'location_id' => $locationId,
                'quantity' => $product->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'pgsql'])) {
            DB::statement('ALTER TABLE products DROP CONSTRAINT products_quantity_nonnegative');
            DB::statement('ALTER TABLE product_batches DROP CONSTRAINT product_batches_quantity_nonnegative');
        }
        Schema::table('stock_outs', fn (Blueprint $table) => $table->dropUnique('stock_outs_idempotency_key_unique'));
        Schema::table('stock_ins', fn (Blueprint $table) => $table->dropUnique('stock_ins_idempotency_key_unique'));
        Schema::dropIfExists('stock_returns');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('product_location_stocks');
        Schema::dropIfExists('locations');
    }
};