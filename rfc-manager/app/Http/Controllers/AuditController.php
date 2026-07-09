<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Rfc;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->with(['user', 'rfc']);

        if ($request->filled('rfc_id')) {
            $query->where('rfc_id', $request->input('rfc_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $auditLogs = $query->orderByDesc('created_at')->paginate(50);

        return Inertia::render('Audit/Index', [
            'auditLogs' => $auditLogs,
            'filters' => $request->only(['rfc_id', 'user_id', 'action', 'date_from', 'date_to']),
            'rfcs' => Rfc::orderByDesc('created_at')->get(['id', 'subject', 'status']),
            'users' => \App\Models\User::orderBy('name')->get(['id', 'name']),
            'actions' => AuditLog::getActions(),
        ]);
    }
}
