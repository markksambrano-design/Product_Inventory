<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLocationStock extends Model
{
    protected $fillable = ['product_id', 'location_id', 'quantity'];
    protected $casts = ['quantity' => 'decimal:2'];
}