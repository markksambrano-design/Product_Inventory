<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReturn extends Model
{
    protected $fillable = ['product_id', 'product_batch_id', 'user_id', 'type', 'disposition', 'quantity', 'reference_number', 'return_date', 'reason', 'remarks'];
    protected $casts = ['quantity' => 'decimal:2', 'return_date' => 'date'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }
}