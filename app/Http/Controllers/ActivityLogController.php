<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $module = $request->module;

        $logs = ActivityLog::with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'user',
                            function ($userQuery) use ($search) {
                                $userQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        );
                });
            })
            ->when($module, function ($query, $module) {
                $query->where('module', $module);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'activity-logs.index',
            compact(
                'logs',
                'search',
                'module'
            )
        );
    }
}
