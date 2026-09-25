<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesClosing extends Model
{
    protected $fillable = ['closing_date', 'closed_by', 'total_sales', 'cash_sales', 'card_sales', 'gcash_sales', 'refunds', 'remarks'];
    protected $casts = ['closing_date' => 'date', 'total_sales' => 'decimal:2', 'cash_sales' => 'decimal:2', 'card_sales' => 'decimal:2', 'gcash_sales' => 'decimal:2', 'refunds' => 'decimal:2'];
}