<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrder extends Model { protected $fillable=['po_number','supplier_id','created_by','status','approval_status','approved_by','approved_at','order_date','expected_date','total_amount','notes','cancellation_reason']; protected $casts=['order_date'=>'date','expected_date'=>'date','approved_at'=>'datetime','total_amount'=>'decimal:2']; public function supplier(){return $this->belongsTo(Supplier::class);} public function items(){return $this->hasMany(PurchaseOrderItem::class);} public function creator(){return $this->belongsTo(User::class,'created_by');} public function approver(){return $this->belongsTo(User::class,'approved_by');} public function receipts(){return $this->hasMany(PurchaseOrderReceipt::class);} }
