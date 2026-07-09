<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRfcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'downtime_possible' => ['required', 'boolean'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'performers' => ['required', 'array', 'min:1'],
            'performers.*' => ['exists:users,id'],
            'performers.*.reminder_minutes' => ['nullable', 'integer', 'min:0'],
            'stakeholders' => ['nullable', 'array'],
            'stakeholders.*' => ['exists:users,id'],
            'approvers' => ['required', 'array', 'min:1'],
            'approvers.*' => ['exists:users,id'],
            'approvers.*.type' => ['nullable', Rule::in(['mandatory', 'optional', 'backup'])],
            'approvers.*.reminder_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'recurrent_reminder_enabled' => ['required', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'performers.required' => 'At least one performer is required.',
            'approvers.required' => 'At least one approver is required.',
        ];
    }
}
