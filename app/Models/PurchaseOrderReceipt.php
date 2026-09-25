<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderReceipt extends Model
{
    protected $fillable = ['purchase_order_id', 'received_by', 'reference_number', 'received_date', 'remarks'];
    protected $casts = ['received_date' => 'date'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}