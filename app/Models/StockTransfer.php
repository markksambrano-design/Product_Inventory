<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = ['reference_number', 'source_location_id', 'destination_location_id', 'user_id', 'status', 'transfer_date', 'remarks'];
    protected $casts = ['transfer_date' => 'date'];

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function sourceLocation()
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }

    public function destinationLocation()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }
}