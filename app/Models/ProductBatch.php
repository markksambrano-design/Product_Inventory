<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'stock_in_id',
        'batch_number',
        'quantity',
        'expiration_date',
        'received_date',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'received_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }

    public function stockOutBatches()
    {
        return $this->hasMany(StockOutBatch::class);
    }

    public function disposals()
    {
        return $this->hasMany(ExpiredProductDisposal::class);
    }
}
