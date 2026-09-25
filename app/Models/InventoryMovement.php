<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id', 'product_batch_id', 'location_id', 'user_id', 'movement_type',
        'quantity', 'source_type', 'source_id', 'idempotency_key', 'movement_date', 'remarks',
    ];

    protected $casts = ['quantity' => 'decimal:2', 'movement_date' => 'date'];

    public function source()
    {
        return $this->morphTo();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}