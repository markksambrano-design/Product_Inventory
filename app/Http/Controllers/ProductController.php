<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ActivityLogger;
use App\Services\LocalProductImageMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $products = Product::with('category')
            ->when($search, function ($query, $search) {
                $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            })
            ->orderByRaw("CASE WHEN image IS NULL OR image = '' THEN 1 ELSE 0 END")
            ->latest()
            ->paginate(10);

        return view('products.index', compact('products', 'search'));
    }

    public function create()
    {
        $categories = $this->activeCategoryTree();
        $generatedBarcode = $this->generateUniqueBarcode();
        $productNames = Product::query()
            ->whereNotNull('product_name')
            ->distinct()
            ->orderBy('product_name')
            ->pluck('product_name');
        $brands = Product::query()
            ->whereNotNull('brand')
            ->where('brand', '<>', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return view('products.create', compact('categories', 'generatedBarcode', 'productNames', 'brands'));
    }

    public function imageMatch(Request $request, LocalProductImageMatcher $imageMatcher)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|min:2|max:255',
            'brand' => 'required|string|min:2|max:255',
        ]);
        $source = $imageMatcher->findSource($validated['product_name'], $validated['brand']);

        return response()->json([
            'matched' => (bool) $source,
            'image_url' => $source ? asset('storage/'.$source).'?v='.filemtime(Storage::disk('public')->path($source)) : null,
        ]);
    }

    public function scan(Request $request)
    {
        $validated = $request->validate(['barcode' => 'required|string|max:255']);
        $product = Product::where('barcode', $validated['barcode'])->first();

        return $product
            ? redirect()->route('products.show', $product)
            : redirect()->route('products.index')->with('error', 'No product found for that barcode.');
    }

    public function label(Product $product)
    {
        abort_if(blank($product->barcode), 404, 'This product has no barcode.');

        return view('products.label', compact('product'));
    }

    public function placeholder(Product $product)
    {
        $name = htmlspecialchars($product->product_name, ENT_XML1);
        $brand = htmlspecialchars($product->brand ?: ($product->category?->name ?? 'Inventory Product'), ENT_XML1);
        $initials = collect(preg_split('/\s+/', $product->product_name))->filter()->take(2)->map(fn ($word) => strtoupper(substr($word, 0, 1)))->implode('');
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
          <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#eef1ff"/><stop offset="1" stop-color="#dce5ff"/></linearGradient></defs>
          <rect width="600" height="600" rx="48" fill="url(#g)"/><circle cx="300" cy="245" r="112" fill="#536dfe" opacity=".12"/><rect x="214" y="159" width="172" height="172" rx="42" fill="#536dfe"/>
          <text x="300" y="273" text-anchor="middle" font-family="Arial" font-size="72" font-weight="700" fill="white">{$initials}</text>
          <text x="300" y="420" text-anchor="middle" font-family="Arial" font-size="28" font-weight="700" fill="#23314d">{$name}</text>
          <text x="300" y="458" text-anchor="middle" font-family="Arial" font-size="20" fill="#68758d">{$brand}</text>
        </svg>
        SVG;

        return response($svg, 200, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'public, max-age=86400']);
    }

    public function store(Request $request, LocalProductImageMatcher $imageMatcher)
    {
        if (blank($request->input('barcode'))) {
            $request->merge(['barcode' => $this->generateUniqueBarcode()]);
        }

        $validated = $request->validate([
            'product_code' => 'required|string|max:100|unique:products,product_code',
            'product_name' => 'required|string|max:255',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('status', true)],
            'brand' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
        ]);

        if (! $request->hasFile('image') && ! $imageMatcher->findSource($validated['product_name'], $validated['brand'] ?? '')) {
            throw ValidationException::withMessages([
                'image' => 'A product image is required. Enter a recognized Product Name and Brand, or upload an image.',
            ]);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // New products always start with zero stock.
        // Stock must be added through Stock In.
        $validated['quantity'] = 0;

        $validated['status'] = $request->has('status');

        $product = Product::create($validated);

        if (! $product->image && ($image = $imageMatcher->matchAndCopy($product))) {
            $product->update(['image' => $image]);
        }

        ActivityLogger::log(
            'Created',
            'Products',
            'Created product: '.$product->product_name
        );

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product added successfully. Use Stock In to add inventory.'
            );
    }

    public function show(Product $product)
    {
        $product->load('category');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = $this->activeCategoryTree();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_code' => 'required|string|max:100|unique:products,product_code,'.$product->id,
            'product_name' => 'required|string|max:255',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('status', true)],
            'brand' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,'.$product->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('products', 'public');

            if ($product->image && ! filter_var($product->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($product->image);
            }

            $validated['image'] = $newImage;
        } else {
            unset($validated['image']);
        }

        $validated['status'] = $request->has('status');

        $product->update($validated);

        ActivityLogger::log(
            'Updated',
            'Products',
            'Updated product: '.$product->product_name
        );

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product updated successfully.'
            );
    }

    public function destroy(Product $product)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Admin access only.');
        }

        if ($product->stockIns()->exists() || $product->stockOuts()->exists() || $product->batches()->exists()) {
            return back()->with('error', 'Cannot delete a product with inventory transaction history. Mark it inactive instead.');
        }

        $productName = $product->product_name;

        $product->delete();

        ActivityLogger::log(
            'Deleted',
            'Products',
            'Deleted product: '.$productName
        );

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product deleted successfully.'
            );
    }

    private function generateUniqueBarcode(): string
    {
        do {
            // 200-299 is commonly reserved for restricted, in-store use.
            $base = '200'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $sum = 0;

            foreach (str_split($base) as $index => $digit) {
                $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
            }

            $barcode = $base.((10 - ($sum % 10)) % 10);
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    private function activeCategoryTree()
    {
        return Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }
}
