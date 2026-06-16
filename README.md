# SimpleTasks API

A production-ready REST API for personal task management built with Laravel 12. Designed as a portfolio and boilerplate reference showcasing modern Laravel patterns: Actions, Domain Events, queued Notifications, Policies, and API versioning.

## Stack

| | |
|---|---|
| **Framework** | Laravel 12, PHP 8.2 |
| **Auth** | Laravel Sanctum 4 |
| **Filtering** | Spatie QueryBuilder |
| **API Docs** | Knuckles Scribe |
| **Testing** | PHPUnit — 116 tests, 264 assertions |
| **Database** | MySQL / PostgreSQL (SQLite for tests) |
| **Queue** | Database driver (sync in tests) |

## Architecture

```
HTTP Request
    └── FormRequest (validation)
         └── Controller (thin, delegates)
              └── Action (single responsibility)
                   ├── Model
                   └── Event → Listener → Notification (queued)
```

- **Actions** — one class per use case (`CreateTaskAction`, `LoginUserAction`, …)
- **Domain Events** — `TaskCreated`, `TaskUpdated`, `TaskDeleted`, `TaskCompleted`, `UserRegistered`
- **Policies** — `TaskPolicy` centralises authorization
- **ApiResponse trait** — consistent `{ data, message }` envelope
- **Global JSON error handler** — always returns JSON on `api/*` routes

---

## Installation

### Requirements

- PHP >= 8.2
- Composer
- MySQL or PostgreSQL

### Steps

```bash
# 1. Clone
git clone https://github.com/PauloFragaDev/simpletasks-api.git
cd simpletasks-api

# 2. Install dependencies
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simpletasks
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migrations and seeders
php artisan migrate --seed

# 6. Start server
php artisan serve
```

The API will be available at `http://localhost:8000/api/v1`.

### Queue worker (for notifications)

```bash
php artisan queue:work
```

> In the test environment `QUEUE_CONNECTION=sync` — notifications run synchronously without a worker.

---

## API Reference

All endpoints are prefixed with `/api/v1`. Protected endpoints require `Authorization: Bearer {token}`.

### Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/register` | Register new user | — |
| `POST` | `/login` | Login (returns token) | — |
| `POST` | `/logout` | Revoke current token | ✓ |
| `GET` | `/me` | Authenticated user profile | ✓ |

### Password Reset

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/forgot-password` | Send reset link by email | — |
| `POST` | `/reset-password` | Reset password with token | — |

### Email Verification

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/auth/verify-email/{id}/{hash}` | Verify email (signed URL) | ✓ |
| `POST` | `/auth/email/resend` | Resend verification email | ✓ |

### Token Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/auth/tokens` | List all active tokens | ✓ |
| `DELETE` | `/auth/tokens/{id}` | Revoke a specific token | ✓ |

### Tasks

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/tasks` | List tasks (filter, sort, paginate) | ✓ |
| `POST` | `/tasks` | Create task | ✓ |
| `GET` | `/tasks/{id}` | Get task | ✓ |
| `PUT/PATCH` | `/tasks/{id}` | Update task | ✓ |
| `DELETE` | `/tasks/{id}` | Soft-delete task | ✓ |

### Notifications

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/notifications` | List notifications (paginated) | ✓ |
| `POST` | `/notifications/{id}/read` | Mark notification as read | ✓ |
| `POST` | `/notifications/read-all` | Mark all as read | ✓ |

---

## Usage Examples

### Register

```http
POST /api/v1/register
Content-Type: application/json

{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

```json
{
  "message": "User registered successfully.",
  "data": {
    "user": { "id": 1, "name": "Jane Doe", "email": "jane@example.com" },
    "token": "1|abc123..."
  }
}
```

### Login

```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "jane@example.com",
  "password": "password123",
  "device_name": "My Laptop"
}
```

```json
{
  "message": "Login successful.",
  "data": {
    "user": { "id": 1, "name": "Jane Doe", "email": "jane@example.com" },
    "token": "2|xyz789..."
  }
}
```

### Create Task

```http
POST /api/v1/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Submit quarterly report",
  "description": "Finance team deadline",
  "status": "pending",
  "priority": "high",
  "due_date": "2026-07-15"
}
```

```json
{
  "message": "Task created successfully.",
  "data": {
    "id": 1,
    "title": "Submit quarterly report",
    "status": "pending",
    "priority": "high",
    "due_date": "2026-07-15",
    "completed_at": null
  }
}
```

### Filter and Sort Tasks

```http
# Filter by status and priority
GET /api/v1/tasks?filter[status]=pending&filter[priority]=high

# Sort ascending by title
GET /api/v1/tasks?sort=title

# Sort descending by due_date
GET /api/v1/tasks?sort=-due_date

# Combined with pagination
GET /api/v1/tasks?filter[status]=in_progress&sort=-created_at&per_page=10
```

Allowed sort fields: `created_at`, `updated_at`, `title`, `due_date`, `priority`, `status`.

---

## Task Fields

| Field | Type | Values | Notes |
|-------|------|--------|-------|
| `title` | string | — | Required on create, max 255 |
| `description` | string | — | Optional |
| `status` | enum | `pending`, `in_progress`, `done` | Default: `pending` |
| `priority` | enum | `low`, `medium`, `high` | Default: `medium` |
| `due_date` | date | — | Optional |
| `completed_at` | datetime | — | Auto-set when status → `done` |

> Marking a task as `done` automatically sets `completed_at`. Reopening it clears the field.

---

## Notifications

Three notification types are dispatched automatically:

| Event | Notification | Channel |
|-------|-------------|---------|
| User registers | `WelcomeNotification` | database |
| Task marked as done | `TaskCompletedNotification` | database |
| Task due today (scheduled) | `TaskDueReminderNotification` | database |

### Scheduled Command

```bash
# Send reminders for all tasks due today (runs daily via scheduler)
php artisan tasks:send-due-reminders

# Start the scheduler
php artisan schedule:run
```

---

## Testing

```bash
# Run the full test suite
php artisan test

# Run a specific file
php artisan test tests/Feature/TaskTest.php
```

The suite covers unit tests for Actions, Policies, and Enums, and feature tests for every endpoint including edge cases, authorization, rate limiting, and notification flows.

---

## Seeded Test Data

After running `php artisan migrate --seed`:

| Field | Value |
|-------|-------|
| Email | `test@example.com` |
| Password | `password123` |

Three sample tasks are seeded for this user.

---

## API Documentation

This project uses [Scribe](https://scribe.knuckles.wtf) for API documentation generation.

```bash
php artisan scribe:generate
```

Documentation is generated at `public/docs/index.html` (gitignored).

---

## License

MIT
