<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'reason',
        'stock_out_date',
        'reference_number',
        'remarks',
        'idempotency_key',
    ];

    protected $casts = [
        'stock_out_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batches()
    {
        return $this->hasMany(StockOutBatch::class);
    }
}
