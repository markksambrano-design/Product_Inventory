<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkProductController extends Controller
{
    public function form()
    {
        $products = Product::with('category')->orderBy('product_name')->get();
        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('products.bulk', compact('products', 'categories'));
    }

    public function import(Request $request)
    {
        $request->validate(['csv' => 'required|file|mimes:csv,txt|max:5120']);
        $handle = fopen($request->file('csv')->getRealPath(), 'r');
        $headers = array_map(fn ($header) => strtolower(trim($header)), fgetcsv($handle));
        $required = ['product_code', 'product_name', 'category', 'unit', 'cost_price', 'selling_price', 'minimum_stock'];

        if (array_diff($required, $headers)) {
            fclose($handle);

            return back()->with('error', 'CSV headers are invalid. Download the template first.');
        }

        $count = 0;
        DB::transaction(function () use ($handle, $headers, &$count): void {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($headers)) {
                    continue;
                }

                $data = array_combine($headers, $row);
                $selectedCategory = Category::firstOrCreate(
                    ['name' => trim($data['category'])],
                    ['description' => 'Imported category', 'status' => true]
                );
                Product::updateOrCreate(
                    ['product_code' => trim($data['product_code'])],
                    [
                        'product_name' => trim($data['product_name']),
                        'category_id' => $selectedCategory->id,
                        'brand' => $data['brand'] ?? null,
                        'unit' => trim($data['unit']),
                        'cost_price' => (float) $data['cost_price'],
                        'selling_price' => (float) $data['selling_price'],
                        'minimum_stock' => (float) $data['minimum_stock'],
                        'quantity' => 0,
                        'barcode' => blank($data['barcode'] ?? null) ? null : trim($data['barcode']),
                        'status' => true,
                        'description' => $data['description'] ?? null,
                    ]
                );
                $count++;
            }

            fclose($handle);
        });

        ActivityLogger::log('Imported', 'Products', "Imported {$count} products from CSV.");

        return back()->with('success', "{$count} products imported or updated.");
    }

    public function template()
    {
        return response()->streamDownload(function (): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['product_code', 'product_name', 'category', 'brand', 'unit', 'cost_price', 'selling_price', 'minimum_stock', 'barcode', 'description']);
            fputcsv($file, ['PRD-1001', 'Sample Product', 'Snacks', 'Sample Brand', 'Piece', '10.00', '15.00', '5', '', 'Optional description']);
            fclose($file);
        }, 'product-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'price_percent' => 'nullable|numeric|min:-99|max:1000',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|in:active,inactive',
        ]);
        $products = Product::whereIn('id', $validated['product_ids'])->get();

        DB::transaction(function () use ($products, $validated): void {
            foreach ($products as $product) {
                $data = [];
                if (isset($validated['price_percent']) && $validated['price_percent'] !== '') {
                    $data['selling_price'] = max(0, (float) $product->selling_price * (1 + (float) $validated['price_percent'] / 100));
                }
                if (! empty($validated['category_id'])) {
                    $data['category_id'] = $validated['category_id'];
                }
                if (! empty($validated['status'])) {
                    $data['status'] = $validated['status'] === 'active';
                }
                if ($data) {
                    $product->update($data);
                }
            }
        });

        ActivityLogger::log('Bulk Updated', 'Products', 'Updated '.$products->count().' products.');

        return back()->with('success', $products->count().' products updated.');
    }
}
