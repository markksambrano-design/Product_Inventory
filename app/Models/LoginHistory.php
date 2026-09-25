<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoginHistory extends Model { protected $fillable=['user_id','email','successful','ip_address','user_agent']; protected $casts=['successful'=>'boolean']; public function user(){return $this->belongsTo(User::class);} }
