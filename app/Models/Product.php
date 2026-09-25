<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    protected static function booted(): void
    {
        static::created(function (Product $product) {
            if (! Schema::hasTable('locations') || ! Schema::hasTable('product_location_stocks')) {
                return;
            }

            $locationId = \App\Models\Location::where('code', 'MAIN')->value('id');
            if ($locationId) {
                \App\Models\ProductLocationStock::create([
                    'product_id' => $product->id,
                    'location_id' => $locationId,
                    'quantity' => $product->quantity,
                ]);
            }
        });
    }

    protected $fillable = [
        'category_id',
        'product_code',
        'product_name',
        'brand',
        'unit',
        'cost_price',
        'selling_price',
        'quantity',
        'minimum_stock',
        'barcode',
        'image',
        'status',
        'description',
    ];

    protected $casts = [
        'status' => 'boolean',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
    ];

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return route('products.placeholder', $this);
        }

        return asset('storage/'.$this->image).'?v='.$this->updated_at?->timestamp;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->category?->name ?? 'N/A';
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }
}
