<?php

namespace App\Http\Controllers;

use App\Models\Rfc;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function approve(Request $request, Rfc $rfc)
    {
        $user = $request->user();

        $approval = $rfc->approvers()->where('user_id', $user->id)->firstOrFail();

        $oldStatus = $approval->pivot->status;
        $approval->pivot->update(['status' => 'approved', 'comments' => null]);

        AuditLog::create([
            'rfc_id' => $rfc->id,
            'user_id' => $user->id,
            'action' => AuditLog::ACTION_APPROVED,
            'old_values' => ['approval_status' => $oldStatus],
            'new_values' => ['approval_status' => 'approved'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $rfc->creator->notify(new RfcApprovalUpdate($rfc, 'approved', $user));

        if ($rfc->isFullyApproved()) {
            $rfc->update(['status' => Rfc::STATUS_APPROVED]);
            $rfc->creator->notify(new \App\Notifications\RfcFullyApproved($rfc));
        }

        return back()->with('success', 'RFC approved.');
    }

    public function reject(Request $request, Rfc $rfc)
    {
        $request->validate([
            'comments' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        $approval = $rfc->approvers()->where('user_id', $user->id)->firstOrFail();

        $oldStatus = $approval->pivot->status;
        $approval->pivot->update(['status' => 'rejected', 'comments' => $request->input('comments')]);

        $rfc->update([
            'status' => Rfc::STATUS_REJECTED,
            'rejection_reason' => $request->input('comments'),
        ]);

        AuditLog::create([
            'rfc_id' => $rfc->id,
            'user_id' => $user->id,
            'action' => AuditLog::ACTION_REJECTED,
            'old_values' => ['approval_status' => $oldStatus, 'rfc_status' => $rfc->status],
            'new_values' => ['approval_status' => 'rejected', 'comments' => $request->input('comments')],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $rfc->creator->notify(new RfcRejected($rfc, $request->input('comments')));

        return back()->with('success', 'RFC rejected. Creator has been notified.');
    }
}
