<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOutBatch extends Model
{
    protected $fillable = [
        'stock_out_id',
        'product_batch_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }
}
