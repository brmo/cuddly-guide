# RFC Manager

A full-featured Request For Change (RFC) management system built with Laravel 11 and Inertia.js. It tracks requests from creation, through approval, notification, scheduling, change execution, to completion.

## Features

- Complete RFC lifecycle management (Draft → Pending Approval → Approved → Scheduled → In Progress → Completed / Rejected / Recalled)
- Role-based access control (Admin, Manager, User)
- Multi-tier approval workflow (mandatory, optional, backup approvers)
- Preferred notification channels (Email, Slack, SMS, Database)
- Full audit log for every state change, approved/rejected status, and comment
- Recall and resubmit capability after rejection or recall
- Scheduled change notifications with 24-hour and 12-hour reminder logic
- Per-performer configurable pre-change reminders (default 30 minutes)
- Scheduled performers/repeat reminders delivered at preferred notification method
- Full history of approvals/rejections with comments
- Global search and filterable audit log UI

## Requirements

- PHP >= 8.2
- Composer
- Node.js & npm (for frontend assets)
- SQLite (or MySQL/PostgreSQL)
- Mail driver (Mailtrap, Sendgrid, etc.)
- Slack Incoming Webhook URL + Bot Token (optional)

## Installation

```bash
git clone <repo-url>
cd rfc-manager
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
```

## Setup

1. **Database**: Configure in `.env` (supports SQLite, MySQL, PostgreSQL)
2. **Mail**: Set up SMTP credentials in `.env`
3. **Slack**: Configure `SLACK_WEBHOOK_URL` and `SLACK_BOT_TOKEN`
4. **Queue**: Configure Redis for production queue processing

## Queue Worker

Start the queue worker for async notifications:

```bash
php artisan queue:work --tries=3
```

## Scheduled Commands

The command `rfcs:send-reminders` runs every 5 minutes via the scheduler:

```bash
php artisan schedule:run
```

## User Roles

- **admin**: Full access including user management
- **manager**: Can view, create, and approve RFCs
- **user**: Can view and create RFCs

## Default Login

After seeding, log in with the default admin user:
- Email: admin@example.com
- Password: password

## Architecture

### Models

- **User**: Registered users, preferred notification method (email/slack/sms), Slack user IDs, phone numbers
- **Rfc**: Main RFC entity with full lifecycle
- **AuditLog**: Complete audit trail with old/new values
- **NotificationLog**: Tracks all sent notifications per channel

### Notifications

- `RfcSubmitted` → approvers via email, Slack
- `RfcRecalled` → approvers
- `RfcApprovalUpdate` → creator on partial approval
- `RfcFullyApproved` → creator when all mandatory approvers approve
- `RfcRejected` → creator with reason
- `RfcScheduled` → stakeholders
- `RfcReminder` → performers and stakeholders before change
- `RfcCompleted` → stakeholders on completion

### Status Flow

```
Draft → Pending Approval → Approved → Scheduled → In Progress → Completed
                                                  \
                                                     Rejected
Ctrl: Recall from Pending Approval
```

### Boarding/Approval

- Mandatory Approvers: All must approve
- Optional Approvers: Reviewed but not required
- Backup Approvers: Substitute for defined scenarios

### Future Enhancements

- Add attachments
- Per-RFC Slack channel integration
- Per-RFC recurrent reminder preferences
- RFC templates
- Global search with Elasticsearch
- Admin dashboard with statistics
