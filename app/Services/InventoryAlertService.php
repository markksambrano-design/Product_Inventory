<?php
namespace App\Services;
use App\Models\InventoryNotification;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\LoginHistory;
class InventoryAlertService {
    public static function syncFor(User $user): void {
        $low=Product::where('status',true)->whereColumn('quantity','<=','minimum_stock')->count();
        $expiry=ProductBatch::where('quantity','>',0)->whereNotNull('expiration_date')->whereDate('expiration_date','<=',today()->addDays(30))->count();
        if($low>0) InventoryNotification::firstOrCreate(['user_id'=>$user->id,'type'=>'low_stock','read_at'=>null],['title'=>'Stock requires attention','message'=>"{$low} product(s) are low or out of stock.",'url'=>route('products.index')]);
        if($expiry>0) InventoryNotification::firstOrCreate(['user_id'=>$user->id,'type'=>'expiration','read_at'=>null],['title'=>'Expiration warning','message'=>"{$expiry} batch(es) are expiring or expired.",'url'=>route('expiration.index')]);
        $overdue=PurchaseOrder::whereIn('status',['ordered','partial'])->whereNotNull('expected_date')->whereDate('expected_date','<',today())->count();
        if($overdue>0) InventoryNotification::firstOrCreate(['user_id'=>$user->id,'type'=>'overdue_po','read_at'=>null],['title'=>'Overdue purchase orders','message'=>"{$overdue} purchase order(s) are overdue.",'url'=>route('purchase-orders.index')]);
        $failed=LoginHistory::where('successful',false)->where('created_at','>=',now()->subDay())->count();
        if($failed>0) InventoryNotification::firstOrCreate(['user_id'=>$user->id,'type'=>'failed_login','read_at'=>null],['title'=>'Failed login attempts','message'=>"{$failed} failed login attempt(s) were recorded in the last 24 hours.",'url'=>route('security.index')]);
    }
}
