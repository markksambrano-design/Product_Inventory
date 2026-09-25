<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BackupHistory extends Model { protected $fillable=['user_id','filename','size','checksum','disk','type']; public function user(){return $this->belongsTo(User::class);} }
