<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpiredProductDisposal extends Model
{
    protected $fillable = ['product_batch_id', 'user_id', 'quantity', 'reason', 'disposal_date', 'remarks'];

    protected $casts = ['quantity' => 'decimal:2', 'disposal_date' => 'date'];

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
