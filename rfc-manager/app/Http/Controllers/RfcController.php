<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRfcRequest;
use App\Http\Requests\UpdateRfcRequest;
use App\Models\Rfc;
use App\Models\User;
use App\Models\AuditLog;
use Inertia\Inertia;
use App\Notifications\RfcSubmitted;
use App\Notifications\RfcRecalled;
use App\Notifications\RfcScheduled;
use App\Notifications\RfcReminder;
use Illuminate\Http\Request;

class RfcController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Rfc::query()->with(['creator', 'performers', 'stakeholders', 'approvers']);

        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            $query->withCount('audits');
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('approvers', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  })
                  ->orWhereHas('performers', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  })
                  ->orWhereHas('stakeholders', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });
        }

        $rfcs = $query->orderByDesc('scheduled_at')->paginate(15);

        return Inertia::render('Rfcs/Index', [
            'rfcs' => $rfcs,
        ]);
    }

    public function create()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'preferred_notification_method']);

        return Inertia::render('Rfcs/Create', [
            'users' => $users,
            'statuses' => Rfc::getStatusOptions(),
            'severities' => Rfc::getSeverityOptions(),
            'priorities' => Rfc::getPriorityOptions(),
        ]);
    }

    public function store(StoreRfcRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        $rfc = Rfc::create(array_merge($data, [
            'created_by' => $user->id,
            'status' => Rfc::STATUS_DRAFT,
        ]));

        $rfc->performers()->sync($data['performers'] ?? []);
        $rfc->stakeholders()->sync($data['stakeholders'] ?? []);
        $rfc->approvers()->sync($data['approvers'] ?? []);

        $this->logAudit($rfc, $user, AuditLog::ACTION_CREATED, null, $data);

        return redirect()->route('rfcs.index')->with('success', 'RFC created successfully.');
    }

    public function edit(Rfc $rfc)
    {
        if ($rfc->isPending() || $rfc->isApproved() || $rfc->isScheduled()) {
            if (!$rfc->isRecalled()) {
                return back()->with('error', 'This RFC cannot be edited in its current status.');
            }
        }

        $users = User::orderBy('name')->get(['id', 'name', 'email', 'preferred_notification_method']);

        return Inertia::render('Rfcs/Edit', [
            'rfc' => $rfc->load('performers', 'stakeholders', 'approvers'),
            'users' => $users,
            'statuses' => Rfc::getStatusOptions(),
            'severities' => Rfc::getSeverityOptions(),
            'priorities' => Rfc::getPriorityOptions(),
        ]);
    }

    public function update(UpdateRfcRequest $request, Rfc $rfc)
    {
        $data = $request->validated();
        $user = $request->user();

        $oldValues = $rfc->only(['subject', 'description', 'severity', 'downtime_possible', 'priority', 'scheduled_at', 'notes']);

        $rfc->update($data);
        $rfc->performers()->sync($data['performers'] ?? []);
        $rfc->stakeholders()->sync($data['stakeholders'] ?? []);
        $rfc->approvers()->sync($data['approvers'] ?? []);

        $this->logAudit($rfc, $user, AuditLog::ACTION_UPDATED, $oldValues, $data);

        return redirect()->route('rfcs.index')->with('success', 'RFC updated successfully.');
    }

    public function show(Rfc $rfc)
    {
        $rfc->load(['creator', 'performers', 'stakeholders', 'approvers.user', 'audits.user']);

        return Inertia::render('Rfcs/Show', [
            'rfc' => $rfc,
        ]);
    }

    public function submit(Request $request, Rfc $rfc)
    {
        $user = $request->user();

        if ($rfc->status !== Rfc::STATUS_DRAFT && $rfc->status !== Rfc::STATUS_RECALLED) {
            return back()->with('error', 'Only drafts or recalled RFCs can be submitted.');
        }

        $oldStatus = $rfc->status;
        $rfc->update(['status' => Rfc::STATUS_PENDING_APPROVAL]);

        $this->logAudit($rfc, $user, AuditLog::ACTION_SUBMITTED, ['status' => $oldStatus], ['status' => Rfc::STATUS_PENDING_APPROVAL]);

        foreach ($rfc->approvers as $approver) {
            $approver->notify(new RfcSubmitted($rfc, $approver));
        }

        return back()->with('success', 'RFC submitted for approval.');
    }

    public function recall(Request $request, Rfc $rfc)
    {
        $user = $request->user();

        if ($rfc->status !== Rfc::STATUS_PENDING_APPROVAL) {
            return back()->with('error', 'Only pending RFCs can be recalled.');
        }

        $oldStatus = $rfc->status;
        $rfc->update(['status' => Rfc::STATUS_RECALLED]);

        $this->logAudit($rfc, $user, AuditLog::ACTION_RECALLED, ['status' => $oldStatus], ['status' => Rfc::STATUS_RECALLED]);

        foreach ($rfc->approvers as $approver) {
            $approver->notify(new RfcRecalled($rfc));
        }

        return back()->with('success', 'RFC recalled successfully.');
    }

    public function resubmit(Request $request, Rfc $rfc)
    {
        $user = $request->user();

        if ($rfc->status !== Rfc::STATUS_REJECTED && $rfc->status !== Rfc::STATUS_RECALLED) {
            return back()->with('error', 'Only rejected or recalled RFCs can be resubmitted.');
        }

        $oldStatus = $rfc->status;
        $rfc->update(['status' => Rfc::STATUS_PENDING_APPROVAL, 'rejection_reason' => null]);

        foreach ($rfc->approvers as $approver) {
            $approver->pivot->update(['status' => 'pending']);
        }

        $this->logAudit($rfc, $user, AuditLog::ACTION_RESUBMITTED, ['status' => $oldStatus], ['status' => Rfc::STATUS_PENDING_APPROVAL]);

        foreach ($rfc->approvers as $approver) {
            $approver->notify(new RfcSubmitted($rfc, $approver));
        }

        return back()->with('success', 'RFC resubmitted for approval.');
    }

    public function schedule(Request $request, Rfc $rfc)
    {
        $user = $request->user();

        if (!$rfc->isFullyApproved()) {
            return back()->with('error', 'All mandatory approvers must approve before scheduling.');
        }

        $data = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $oldStatus = $rfc->status;
        $rfc->update(array_merge($data, ['status' => Rfc::STATUS_SCHEDULED]));

        $this->logAudit($rfc, $user, AuditLog::ACTION_SCHEDULED, ['status' => $oldStatus], ['status' => Rfc::STATUS_SCHEDULED, 'scheduled_at' => $data['scheduled_at']]);

        foreach ($rfc->stakeholders as $stakeholder) {
            $stakeholder->notify(new RfcScheduled($rfc));
        }

        foreach ($rfc->performers as $performer) {
            if ($performer->pivot->reminder_minutes > 0) {
                \App\Jobs\SendRfcReminder::dispatch($rfc, $user, $performer, $performer->pivot->reminder_minutes)
                    ->delay($rfc->scheduled_at->subMinutes($performer->pivot->reminder_minutes));
            }
        }

        return back()->with('success', 'RFC scheduled successfully. Notifications sent to stakeholders.');
    }

    public function start(Request $request, Rfc $rfc)
    {
        $user = $request->user();

        if ($rfc->status !== Rfc::STATUS_SCHEDULED) {
            return back()->with('error', 'Only scheduled RFCs can be started.');
        }

        $oldStatus = $rfc->status;
        $rfc->update(['status' => Rfc::STATUS_IN_PROGRESS, 'change_started_at' => now()]);

        $this->logAudit($rfc, $user, AuditLog::ACTION_STARTED, ['status' => $oldStatus], ['status' => Rfc::STATUS_IN_PROGRESS]);

        return back()->with('success', 'Change started.');
    }

    public function complete(Request $request, Rfc $rfc)
    {
        $user = $request->user();

        if ($rfc->status !== Rfc::STATUS_IN_PROGRESS) {
            return back()->with('error', 'Only in-progress RFCs can be completed.');
        }

        $oldStatus = $rfc->status;
        $rfc->update(['status' => Rfc::STATUS_COMPLETED, 'change_completed_at' => now()]);

        $this->logAudit($rfc, $user, AuditLog::ACTION_COMPLETED, ['status' => $oldStatus], ['status' => Rfc::STATUS_COMPLETED]);

        foreach ($rfc->stakeholders as $stakeholder) {
            $stakeholder->notify(new \App\Notifications\RfcCompleted($rfc));
        }

        return back()->with('success', 'RFC marked as completed.');
    }

    protected function logAudit(Rfc $rfc, User $user, string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'rfc_id' => $rfc->id,
            'user_id' => $user->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
