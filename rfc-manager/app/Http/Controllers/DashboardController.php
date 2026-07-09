<?php

namespace App\Http\Controllers;

use App\Models\Rfc;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = request()->user();

        $stats = [
            'total' => Rfc::count(),
            'draft' => Rfc::where('status', Rfc::STATUS_DRAFT)->count(),
            'pending_approval' => Rfc::where('status', Rfc::STATUS_PENDING_APPROVAL)->count(),
            'approved' => Rfc::where('status', Rfc::STATUS_APPROVED)->count(),
            'scheduled' => Rfc::where('status', Rfc::STATUS_SCHEDULED)->count(),
            'in_progress' => Rfc::where('status', Rfc::STATUS_IN_PROGRESS)->count(),
            'completed' => Rfc::where('status', Rfc::STATUS_COMPLETED)->count(),
            'rejected' => Rfc::where('status', Rfc::STATUS_REJECTED)->count(),
        ];

        $recent = Rfc::with('creator')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'subject', 'status', 'created_at', 'created_by', 'scheduled_at']);

        $myApprovals = Rfc::whereHas('approvers', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'pending');
        })->count();

        $upcoming = Rfc::scheduled()
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get(['id', 'subject', 'scheduled_at', 'status']);

        $recentAudits = AuditLog::with(['user', 'rfc'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentRfcs' => $recent,
            'myPendingApprovals' => $myApprovals,
            'upcomingChanges' => $upcoming,
            'recentAudits' => $recentAudits,
        ]);
    }
}
