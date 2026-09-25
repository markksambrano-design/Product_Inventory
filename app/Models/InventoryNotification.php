<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventoryNotification extends Model { protected $fillable=['user_id','type','title','message','url','read_at','email_enabled']; protected $casts=['read_at'=>'datetime','email_enabled'=>'boolean']; public function user(){return $this->belongsTo(User::class);} }
