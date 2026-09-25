<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = ['sale_id', 'sale_item_id', 'batch_id', 'user_id', 'quantity', 'refund_amount', 'reason'];
    protected $casts = ['quantity' => 'decimal:2', 'refund_amount' => 'decimal:2'];
}