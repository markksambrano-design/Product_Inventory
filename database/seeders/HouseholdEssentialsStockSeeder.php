<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductLocationStock;
use App\Models\ProductBatch;
use App\Models\StockIn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HouseholdEssentialsStockSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $mainLocationId = Location::where('code', 'MAIN')->value('id');
            $stockQuantities = [24, 16, 31, 12, 27, 19, 36, 22, 14, 29, 18, 33, 11, 26, 17, 38, 21, 15, 30, 23, 9];
            $costPrices = [42, 45, 48, 52, 55, 58, 39, 43, 61, 28, 31, 35, 25, 67, 72, 37, 41, 46, 49, 53, 59];
            $sellingPrices = [58, 62, 66, 72, 76, 82, 55, 60, 85, 40, 44, 49, 36, 92, 99, 52, 57, 64, 68, 74, 81];

            Product::where('product_code', 'like', 'HE-DWL-%')->orderBy('id')->each(function (Product $product, int $index) use ($mainLocationId, $stockQuantities, $costPrices, $sellingPrices) {
                $quantity = $stockQuantities[$index];
                $restockQuantity = 100;
                $reference = 'HE-SEED-IN-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
                $batchNumber = 'HE-BATCH-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
                $cost = $costPrices[$index];
                $selling = $sellingPrices[$index];
                $receivedDate = today();
                $expirationDate = today()->addYear();

                $product->update([
                    'cost_price' => $cost,
                    'selling_price' => $selling,
                    'quantity' => $quantity,
                ]);

                $stockIn = StockIn::updateOrCreate(
                    ['reference_number' => $reference],
                    [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'cost_price' => $cost,
                        'batch_number' => $batchNumber,
                        'expiration_date' => $expirationDate,
                        'stock_in_date' => $receivedDate,
                        'remarks' => 'Initial household essentials sample stock.',
                    ]
                );

                $batch = ProductBatch::updateOrCreate(
                    ['product_id' => $product->id, 'batch_number' => $batchNumber],
                    [
                        'stock_in_id' => $stockIn->id,
                        'quantity' => $quantity,
                        'expiration_date' => $expirationDate,
                        'received_date' => $receivedDate,
                    ]
                );

                InventoryMovement::updateOrCreate(
                    ['idempotency_key' => 'household-seed-'.$product->id],
                    [
                        'product_id' => $product->id,
                        'product_batch_id' => $batch->id,
                        'movement_type' => 'stock_in',
                        'quantity' => $quantity,
                        'source_type' => StockIn::class,
                        'source_id' => $stockIn->id,
                        'movement_date' => $receivedDate,
                        'remarks' => 'Initial household essentials sample stock.',
                    ]
                );

                if ($mainLocationId) {
                    ProductLocationStock::updateOrCreate(
                        ['product_id' => $product->id, 'location_id' => $mainLocationId],
                        ['quantity' => $quantity]
                    );
                }

                $restockReference = 'HE-RESTOCK-IN-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
                $restockBatchNumber = 'HE-RESTOCK-BATCH-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
                $restockStockIn = StockIn::updateOrCreate(
                    ['reference_number' => $restockReference],
                    [
                        'product_id' => $product->id,
                        'quantity' => $restockQuantity,
                        'cost_price' => $cost,
                        'batch_number' => $restockBatchNumber,
                        'expiration_date' => $expirationDate,
                        'stock_in_date' => $receivedDate,
                        'remarks' => 'Additional household essentials sample restock.',
                    ]
                );

                $restockBatch = ProductBatch::updateOrCreate(
                    ['product_id' => $product->id, 'batch_number' => $restockBatchNumber],
                    [
                        'stock_in_id' => $restockStockIn->id,
                        'quantity' => $restockQuantity,
                        'expiration_date' => $expirationDate,
                        'received_date' => $receivedDate,
                    ]
                );

                InventoryMovement::updateOrCreate(
                    ['idempotency_key' => 'household-restock-'.$product->id],
                    [
                        'product_id' => $product->id,
                        'product_batch_id' => $restockBatch->id,
                        'movement_type' => 'stock_in',
                        'quantity' => $restockQuantity,
                        'source_type' => StockIn::class,
                        'source_id' => $restockStockIn->id,
                        'movement_date' => $receivedDate,
                        'remarks' => 'Additional household essentials sample restock.',
                    ]
                );

                $product->update(['quantity' => $quantity + $restockQuantity]);

                if ($mainLocationId) {
                    ProductLocationStock::updateOrCreate(
                        ['product_id' => $product->id, 'location_id' => $mainLocationId],
                        ['quantity' => $quantity + $restockQuantity]
                    );
                }
            });
        });

        $this->command?->info('Household Essentials sample stock is ready.');
    }
}