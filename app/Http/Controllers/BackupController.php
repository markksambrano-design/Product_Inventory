<?php

namespace App\Http\Controllers;

use App\Models\BackupHistory;
use App\Services\ActivityLogger;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BackupController extends Controller
{
    public function index(){ $backups=BackupHistory::with('user')->latest()->paginate(20); return view('backups.index',compact('backups')); }
    public function create(){ $backup=BackupService::create(auth()->id()); ActivityLogger::log('Backup','System','Created '.$backup->filename); return back()->with('success','Backup created successfully.'); }
    public function download(){ $backup=BackupService::create(auth()->id()); return Storage::disk($backup->disk)->download($backup->filename,basename($backup->filename)); }
    public function file(BackupHistory $backup){ abort_unless(Storage::disk($backup->disk ?: 'local')->exists($backup->filename),404); return Storage::disk($backup->disk ?: 'local')->download($backup->filename,basename($backup->filename)); }
    public function restore(Request $request){
        $request->validate(['backup'=>'required|file|mimes:json,txt|max:20480','dry_run'=>'nullable|boolean']);
        $payload=file_get_contents($request->file('backup')->getRealPath());
        try{$data=BackupService::decode($payload);}catch(\Throwable $e){throw ValidationException::withMessages(['backup'=>'Invalid or unreadable inventory backup file.']);}
        if($request->boolean('dry_run')) return back()->with('success','Restore preview valid: '.count($data['tables']).' tables found; live data was not changed.');
        BackupService::create(auth()->id(),'scheduled');$safe=array_values(array_diff(BackupService::TABLES,['users','activity_logs','login_histories','inventory_notifications']));Schema::disableForeignKeyConstraints();try{DB::transaction(function()use($data,$safe){foreach(array_reverse($safe) as $table)if(Schema::hasTable($table)&&array_key_exists($table,$data['tables']))DB::table($table)->delete();foreach($safe as $table)if(Schema::hasTable($table)&&!empty($data['tables'][$table]))foreach(array_chunk($data['tables'][$table],200) as $rows)DB::table($table)->insert($rows);});}finally{Schema::enableForeignKeyConstraints();}BackupHistory::create(['user_id'=>auth()->id(),'filename'=>$request->file('backup')->getClientOriginalName(),'size'=>strlen($payload),'checksum'=>hash('sha256',$payload),'disk'=>'local','type'=>'restored']);ActivityLogger::log('Restored','System','Restored inventory data from backup.');return back()->with('success','Inventory backup restored.');
    }
}
