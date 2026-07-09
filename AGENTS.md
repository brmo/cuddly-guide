# AGENTS.md

## Project Overview

**Name:** RFC Manager  
**Type:** Laravel 11 web application  
**Purpose:** Full lifecycle management of Requests For Change (RFC) from creation through approval, scheduling, execution, and completion, with mandatory audit trails and configurable notifications.

## Directory Structure

All source files live under `rfc-manager/`.

```
rfc-manager/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── SendScheduledReminders.php   # artisan rfcs:send-reminders
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   ├── DashboardController.php
│   │   │   ├── RfcController.php
│   │   │   ├── ApprovalController.php
│   │   │   ├── UserController.php
│   │   │   ├── AuditController.php
│   │   │   └── AuthController.php
│   │   ├── Middleware/
│   │   │   ├── HandleInertiaRequests.php
│   │   │   ├── AuditMiddleware.php
│   │   │   └── RedirectIfAuthenticated.php
│   │   └── Requests/
│   │       ├── StoreRfcRequest.php
│   │       ├── UpdateRfcRequest.php
│   │       └── StoreUserRequest.php
│   ├── Jobs/
│   │   ├── SendRfcReminder.php
│   │   └── SendStakeholderReminder.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Rfc.php
│   │   ├── AuditLog.php
│   │   └── NotificationLog.php
│   ├── Notifications/
│   │   ├── RfcSubmitted.php
│   │   ├── RfcRecalled.php
│   │   ├── RfcScheduled.php
│   │   ├── RfcApprovalUpdate.php
│   │   ├── RfcFullyApproved.php
│   │   ├── RfcRejected.php
│   │   ├── RfcReminder.php
│   │   └── RfcCompleted.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── RouteServiceProvider.php
│       └── AuthServiceProvider.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── auth.php
│   └── services.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_rfc_pivot_tables.php
│   │   ├── 2024_01_01_000003_create_audit_logs_table.php
│   │   └── 2024_01_01_000004_create_notification_logs_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleAndPermissionSeeder.php
│       └── UserSeeder.php
├── public/
│   └── index.php
├── resources/
│   ├── css/app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── Layouts/
│   │       └── AppLayout.vue
│   ├── Pages/
│   │   ├── auth/Login.vue
│   │   ├── Dashboard.vue
│   │   ├── Rfcs/{Index,Create,Edit,Show}.vue
│   │   ├── Users/{Index,Create,Edit}.vue
│   │   └── Audit/Index.vue
│   └── views/app.blade.php
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── console.php
│   └── commands.php
├── storage/
├── tests/
├── artisan
├── composer.json
├── package.json
├── server.php
├── vite.config.js
└── .env.example
```

## Technology Stack

- **Backend:** Laravel 11 + PHP 8.2+
- **Frontend:** Inertia.js + Vue 3 + Tailwind CSS 4
- **Auth/RBAC:** Laravel Sanctum + spatie/laravel-permission
- **Queue:** sync by default, Redis recommended for production
- **Notifications:** Laravel Notifications (Mail, Slack, Database)

## Key Domain Concepts

### RFC States

`draft → pending_approval → approved → scheduled → in_progress → completed`  
With branches: `rejected` from `pending_approval`, `recalled` from `pending_approval`

### Roles

- `admin` — full access
- `manager` — view/create/approve/audit
- `user` — view/create

### Approver Types

- `mandatory` — all must approve before RFC can be approved
- `optional` — reviewed but not blocking
- `backup` — substitute approvers

### Notification Channels

Preferred method per user: `email`, `slack`, or `sms`.
All notifications also log to the `database` channel.

## Critical Business Rules

1. **Edit lock:** An RFC in `pending_approval`, `approved`, or `scheduled` cannot be edited unless it is `recalled` first.
2. **Recall:** Only allowed from `pending_approval`. Nofities all approvers.
3. **Resubmit:** Allowed from `rejected` or `recalled`. Resets approver pivot `status` to `pending`.
4. **Approval gating:** `isFullyApproved()` checks that all `mandatory` approvers have `approved`. Only then can the status move to `approved`.
5. **24h reminder logic:** When scheduled, if `scheduled_at` is within 24 hours, stakeholders get one notification. If over 24 hours, they get a reminder at `scheduled_at - 12h`.
6. **Performer reminders:** Each performer has `reminder_minutes` (default 30). Jobs are dispatched to fire at `scheduled_at - X minutes`.
7. **Audit:** Every state transition, approval, rejection, recall, resubmit, schedule, start, and complete writes to `audit_logs` with `old_values`, `new_values`, `ip_address`, and `user_agent`.
8. **Notifications:** Sent via queued `ShouldQueue` notifications so they do not block the HTTP request.

## Configuration

- `.env.example` — all environment variables. Copy to `.env`.
- Required for production: Mail SMTP, Slack webhook/token, Queue driver.
- APP_KEY must be set via `php artisan key:generate`.

## Setup Commands

```bash
cd rfc-manager
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Queue / Scheduler

```bash
php artisan queue:work --tries=3
php artisan schedule:run
```

Scheduled command `rfcs:send-reminders` runs every 5 minutes.

## Default Credentials

- Email: `admin@example.com`
- Password: `password`

## Git

Remote: `https://github.com/brmo/cuddly-guide.git`  
Active branch: `session/agent_42015144-f240-430b-bb4b-293edbc366fe`  
Repo root: parent of `rfc-manager/` directory.

## Important Notes for Future Agents

- Do not commit secrets. If `.env.example` is modified, verify no real webhook/token values are present.
- Frontend is Inertia/Vue, not Blade-rendered pages.
- Approval logic lives in `ApprovalController`; lifecycle workflow in `RfcController`.
- The `AuditLog` model must always receive `rfc_id`, `user_id`, `action`, and JSON `old_values`/`new_values` arrays.
- `SendScheduledReminders` handles reminders and automatic state transitions (e.g., moving to `in_progress` at scheduled time). Review carefully before changing.
- `RfcController::edit()` enforces the edit-lock rule; do not bypass without explicit business requirement.
