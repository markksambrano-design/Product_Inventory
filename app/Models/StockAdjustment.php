<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'product_id',
        'product_batch_id',
        'type',
        'quantity',
        'reason',
        'adjustment_date',
        'remarks',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }
}
