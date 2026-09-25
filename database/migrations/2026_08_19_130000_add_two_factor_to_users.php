<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void{Schema::table('users',function(Blueprint $t){$t->boolean('two_factor_enabled')->default(false);$t->string('two_factor_code')->nullable();$t->timestamp('two_factor_expires_at')->nullable();});}public function down():void{Schema::table('users',fn(Blueprint $t)=>$t->dropColumn(['two_factor_enabled','two_factor_code','two_factor_expires_at']));}};
