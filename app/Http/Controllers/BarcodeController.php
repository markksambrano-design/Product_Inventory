<?php
namespace App\Http\Controllers;
use App\Models\Product; use Illuminate\Http\Request;
class BarcodeController extends Controller { public function index(){ $products=Product::whereNotNull('barcode')->orderBy('product_name')->get();return view('barcodes.index',compact('products')); } public function print(Request $r){$v=$r->validate(['product_ids'=>'required|array|min:1','product_ids.*'=>'exists:products,id','mode'=>'required|in:barcode,qr','copies'=>'required|integer|min:1|max:20']);$products=Product::whereIn('id',$v['product_ids'])->get();return view('barcodes.print',['products'=>$products,'mode'=>$v['mode'],'copies'=>$v['copies']]);} }
