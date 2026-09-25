<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrderItem extends Model { protected $fillable=['purchase_order_id','product_id','quantity','received_quantity','unit_cost','expiration_date']; protected $casts=['expiration_date'=>'date','quantity'=>'decimal:2','received_quantity'=>'decimal:2','unit_cost'=>'decimal:2']; public function purchaseOrder(){return $this->belongsTo(PurchaseOrder::class);} public function product(){return $this->belongsTo(Product::class);} }
