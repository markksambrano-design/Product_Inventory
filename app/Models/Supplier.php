<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }
}
