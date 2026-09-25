<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $fillable = [
        'product_id',
        'supplier_id',
        'quantity',
        'cost_price',
        'batch_number',
        'expiration_date',
        'reference_number',
        'stock_in_date',
        'remarks',
        'idempotency_key',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'stock_in_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function batch()
    {
        return $this->hasOne(ProductBatch::class);
    }
}
