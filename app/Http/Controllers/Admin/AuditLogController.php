<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::where('tenant_id', auth()->user()->tenant_id);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->with('user')
            ->latest()
            ->paginate(30);

        $actions = ['created', 'updated', 'deleted', 'login', 'logout'];
        $modelTypes = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->distinct('model_type')
            ->pluck('model_type');

        return Inertia::render('Admin/AuditLogs/Index', compact('logs', 'actions', 'modelTypes'));
    }

    public function show(AuditLog $auditLog)
    {
        if ($auditLog->tenant_id !== auth()->user()->tenant_id) abort(403);

        $auditLog->load('user');

        return Inertia::render('Admin/AuditLogs/Show', compact('auditLog'));
    }
}
