<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sale extends Model { protected $fillable=['sale_number','user_id','status','idempotency_key','subtotal','discount','discount_approved_by','total','cash_received','change_due','payment_method','sold_at']; protected $casts=['sold_at'=>'datetime','subtotal'=>'decimal:2','discount'=>'decimal:2','total'=>'decimal:2','cash_received'=>'decimal:2','change_due'=>'decimal:2']; public function items(){return $this->hasMany(SaleItem::class);} public function user(){return $this->belongsTo(User::class);} public function returns(){return $this->hasMany(SaleReturn::class);} }
