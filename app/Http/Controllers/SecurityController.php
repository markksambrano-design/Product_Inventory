<?php
namespace App\Http\Controllers;
use App\Models\LoginHistory;
class SecurityController extends Controller { public function index(){ $histories=LoginHistory::with('user')->latest()->paginate(30); return view('security.index',compact('histories')); } }
