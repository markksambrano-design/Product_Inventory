<?php
namespace App\Http\Controllers;
use App\Models\InventoryNotification;
use App\Services\InventoryAlertService;
use Illuminate\Http\Request;
class NotificationController extends Controller {
    public function index(){InventoryAlertService::syncFor(auth()->user());$notifications=InventoryNotification::where('user_id',auth()->id())->latest()->paginate(20);return view('notifications.index',compact('notifications'));}
    public function read(InventoryNotification $notification){abort_unless($notification->user_id===auth()->id(),403);$notification->update(['read_at'=>now()]);return $notification->url?redirect($notification->url):back();}
    public function readAll(){InventoryNotification::where('user_id',auth()->id())->whereNull('read_at')->update(['read_at'=>now()]);return back()->with('success','All notifications marked as read.');}
    public function preferences(Request $request){$request->validate(['email_enabled'=>'nullable|boolean']);InventoryNotification::where('user_id',auth()->id())->update(['email_enabled'=>$request->boolean('email_enabled')]);return back()->with('success','Notification preference updated.');}
}
