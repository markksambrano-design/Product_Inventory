<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCount extends Model
{
    protected $fillable = ['product_batch_id', 'user_id', 'system_quantity', 'actual_quantity', 'variance', 'count_date', 'remarks'];
    protected $casts = ['system_quantity' => 'decimal:2', 'actual_quantity' => 'decimal:2', 'variance' => 'decimal:2', 'count_date' => 'date'];
    public function productBatch() { return $this->belongsTo(ProductBatch::class); }
    public function user() { return $this->belongsTo(User::class); }
}
