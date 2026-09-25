<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockIn;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleInventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $suppliers = collect([
                ['supplier_name' => 'Metro Food Distributors', 'contact_person' => 'Anna Reyes', 'phone' => '0917-555-0142', 'email' => 'orders@metrofood.test', 'address' => 'Quezon City'],
                ['supplier_name' => 'Luzon Consumer Goods', 'contact_person' => 'Marco Santos', 'phone' => '0918-555-0187', 'email' => 'sales@luzongoods.test', 'address' => 'Pasig City'],
                ['supplier_name' => 'Freshway Trading', 'contact_person' => 'Lea Cruz', 'phone' => '0919-555-0124', 'email' => 'supply@freshway.test', 'address' => 'Makati City'],
            ])->map(fn ($data) => Supplier::updateOrCreate(
                ['supplier_name' => $data['supplier_name']],
                $data + ['status' => true]
            ));

            $items = [
                ['Rice & Grains','SMP-001','Premium Jasmine Rice','Golden Grain','sack',1250,1495,24,8,180],
                ['Coffee & Powdered Drinks','SMP-002','Instant Coffee 3-in-1','Kape House','box',118,145,7,10,240],
                ['Beverages','SMP-003','Purified Water 500ml','Aqua Fresh','case',165,210,38,12,365],
                ['Beverages','SMP-004','Orange Juice 1L','Sun Valley','bottle',72,95,5,8,12],
                ['Dairy','SMP-005','Fresh Milk 1L','Daily Farm','carton',68,86,14,6,6],
                ['Dairy','SMP-006','Cheddar Cheese 200g','Creamfield','pack',92,120,0,5,60],
                ['Frozen Foods','SMP-007','Chicken Nuggets 1kg','Frost Bite','pack',185,235,16,6,90],
                ['Bakery','SMP-008','Whole Wheat Bread','Bake House','loaf',48,65,4,6,3],
                ['Snacks','SMP-009','Potato Chips Family Pack','Crunch Time','pack',52,69,29,10,150],
                ['Personal Care','SMP-010','Herbal Shampoo 400ml','Pure Care','bottle',96,129,11,5,540],
                ['Household/Cleaning','SMP-011','Dishwashing Liquid 1L','Clean Plus','bottle',78,105,3,7,720],
                ['Meat & Poultry','SMP-012','Frozen Chicken Breast 1kg','Farm Select','pack',210,265,9,5,-2],
            ];

            foreach ($items as $index => [$categoryName,$code,$name,$brand,$unit,$cost,$price,$quantity,$minimum,$expiryDays]) {
                $category = Category::where('name', $categoryName)->firstOrFail();
                $product = Product::updateOrCreate(
                    ['product_code' => $code],
                    ['category_id'=>$category->id,'product_name'=>$name,'brand'=>$brand,'unit'=>$unit,'cost_price'=>$cost,'selling_price'=>$price,'quantity'=>$quantity,'minimum_stock'=>$minimum,'barcode'=>'480900'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),'status'=>true,'description'=>'Sample inventory item for demonstration.']
                );

                $reference = 'SEED-IN-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
                $batchNumber = 'BATCH-'.now()->format('Ym').'-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
                $receivedDate = now()->subDays(20 - ($index % 10))->toDateString();
                $expirationDate = now()->addDays($expiryDays)->toDateString();

                $stockIn = StockIn::updateOrCreate(
                    ['reference_number' => $reference],
                    ['product_id'=>$product->id,'supplier_id'=>$suppliers[$index % $suppliers->count()]->id,'quantity'=>$quantity,'cost_price'=>$cost,'batch_number'=>$batchNumber,'expiration_date'=>$expirationDate,'stock_in_date'=>$receivedDate,'remarks'=>'Initial sample stock.']
                );

                ProductBatch::updateOrCreate(
                    ['product_id'=>$product->id,'batch_number'=>$batchNumber],
                    ['stock_in_id'=>$stockIn->id,'quantity'=>$quantity,'expiration_date'=>$expirationDate,'received_date'=>$receivedDate]
                );
            }
        });
    }
}
