# Event Reminder Application - Detailed Technical Overview

This document provides an exhaustive, low-level technical analysis of the Event Reminder Application. It covers the system architecture, database interactions, API endpoints, cron operations, user interfaces, and core business flows.

---

## 1. System Architecture & Tech Stack
- **Frontend & Admin Panels:** HTML5/CSS3 with vanilla JavaScript (`assets/js/script.js` for tabs) and PHP 8.x for server-side rendering. The UI employs a custom styling system (`assets/css/style.css`) with CSS variables (`--primary-color`, `--danger-color`, etc.) for consistent component styling (buttons, cards, sidebars).
- **Backend Environment:** PHP 8.x running via Apache (`xampp/htdocs`), managing routing, user sessions (`session_start()`), MySQL database connections (`mysqli`), and mail delivery (`PHPMailer`).
- **AI Microservice:** Python 3.x with FastAPI (`python_api/main.py`), utilizing `PyMuPDF` (`fitz`) for PDF text extraction and `google.generativeai` (Gemini 2.5 Flash) for structured JSON data extraction.
- **Task Scheduling:** Background reminders are triggered via APScheduler embedded in the FastAPI application (every 5 minutes), which spawns a subprocess to execute `php send_reminders.php` on the CLI.

---

## 2. Directory & Component Breakdown

### A. Core Application & Routing (Root Directory)
- **`index.php`**: Login view. Contains forms mapping to `auth/login_action.php`. Handles GET parameters for UI feedback (`?error=...`, `?success=...`).
- **`register.php`**: Registration view mapping to `auth/register_action.php`. Features password confirmation validation.
- **`send_reminders.php`**: The cron worker script. Queries the `events` table for entries approaching `reminder1_date` (24h before) and `reminder2_date` (1h before). Tracks email dispatches via boolean flags (`reminder1_sent`, `reminder2_sent`) using PHPMailer. Can be executed via web (POST request from admin) or CLI (`php_sapi_name() === 'cli'`).
- **`check_pending.php`**: Queries `ai_pending_events` to notify administrators of pending calendar approvals.
- **`test_mail.php` & `db_test.php`**: Utilities for environment verification.

### B. Configuration (`/config`)
- **`db.php`**: Initializes `$conn = new mysqli(...)`.
- **`mailer.php`**: Instantiates `PHPMailer\PHPMailer\PHPMailer`. Configures SMTP settings (Host, Port, Username, Password, SMTPSecure). Includes the `sendReminderEmail()` wrapper function used by the cron job.
- **`ai_config.php`**: Defines constants like `AI_API_URL` (typically `http://127.0.0.1:8000/extract-events`) and timeout settings.

### C. Authentication & Security (`/auth`)
- **`auth_check.php`**: Enforces session presence (`isset($_SESSION['user_id'])`). Redirects unauthenticated traffic to `index.php`.
- **`role_check.php`**: Enforces Authorization. Compares `$_SESSION['role']` against `$required_role`.
- **`csrf.php`**: Generates a cryptographically secure token stored in `$_SESSION['csrf_token']`. Provides `csrf_input()` to embed hidden fields in forms, and `verify_csrf_token()` to validate incoming POST requests.
- **`login_action.php`**: Verifies email/password via `password_verify()`. On success, sets `$_SESSION['user_id']`, `$_SESSION['role']`, and `$_SESSION['name']`.
- **`register_action.php`**: Secures passwords using `password_hash($password, PASSWORD_DEFAULT)`. Inserts to the `users` table.

### D. User Interface (`/user`)
- **`dashboard.php`**: The primary user hub. Queries three separate datasets:
  1. *Upcoming Events*: `user_id = ? AND event_date >= NOW()`
  2. *Past Events*: `user_id = ? AND event_date < NOW()`
  3. *Global Events*: `is_global = 1 AND event_date >= NOW()`
  Uses a JS-driven tab system to toggle between these lists.
- **`add_event.php` & `edit_event.php`**: CRUD views for personal events. Automatically calculates and stores `reminder1_date` (-24 hours) and `reminder2_date` (-1 hour) based on the user's `event_date` input.
- **`delete_event.php`**: Deletes events strictly where `id = ? AND user_id = ?` to prevent cross-account deletion.

### E. Administrator Interface (`/admin`)
- **`dashboard.php`**: Displays aggregate metrics (Total Users, Total Events, Pending AI Events). Contains a secure POST button to manually trigger `send_reminders.php`.
- **`upload_calendar.php`**: Multipart form allowing administrators to upload `.pdf` academic calendars to the `/uploads` directory.
- **`review_ai_events.php`**: Lists records from `ai_pending_events`. Admins can hit "Approve", which migrates the record into the `events` table (setting `is_global = 1` and `user_id = NULL`), or "Reject" to delete it.

### F. AI Service & Integrations (`/ai` & `/python_api`)
- **`ai/ai_event_extractor.php`**: The PHP bridge. Uses PHP `cURL` to send a local PDF via a `CURLFile` object to the Python API. Parses the returning JSON array and bulk-inserts records into the `ai_pending_events` table using the `storePendingEvent()` helper from `pending_events.php`.
- **`python_api/main.py`**:
  - `POST /extract-events`: Receives the PDF payload. Uses `PyMuPDF` to convert binary PDF into raw text. Injects the raw text into a strict Gemini prompt: `"Return ONLY a valid JSON array... Each element must be a JSON object with exactly these three keys: 'title', 'description', 'event_date'."`
  - *APScheduler*: Defines an `@asynccontextmanager` lifespan event that runs `BackgroundScheduler`. It executes `subprocess.run(["php", "send_reminders.php"])` every 5 minutes, allowing the system to run asynchronously without Linux cron configuration.

---

## 3. Database Schema Layout (`/database/schema.sql`)
1. **`users` Table:**
   - `id` (PK, INT AUTO_INCREMENT)
   - `name`, `email` (UNIQUE), `password` (VARCHAR 255 for hashes)
   - `role` (ENUM: 'user', 'admin')
2. **`events` Table:**
   - `id` (PK)
   - `user_id` (FK referencing `users(id)`, NULL if global)
   - `title`, `description`
   - `event_date`, `reminder1_date`, `reminder2_date`
   - `reminder1_sent` (BOOLEAN DEFAULT 0), `reminder2_sent` (BOOLEAN DEFAULT 0)
   - `is_global` (BOOLEAN DEFAULT 0)
3. **`ai_pending_events` Table:**
   - `id` (PK)
   - `title`, `description`, `event_date`
   - `status` (ENUM: 'pending', 'approved', 'rejected')
   - `source_file` (VARCHAR) tracking the origin PDF.

---

## 4. Complex Business Logic Flows

### Event Notification Logic (`send_reminders.php`)
When triggered (by Python or Admin), the script performs four distinct SQL queries:
1. **Personal Reminder 1 (24h):** Fetch events where `reminder1_sent = 0`, `reminder1_date <= NOW()`, and `event_date > NOW()`. Send email to `user_id`, update `reminder1_sent = 1`.
2. **Personal Reminder 2 (1h):** Same logic, but using `reminder2` columns.
3. **Global Reminder 1:** Fetch global events. Fetch *all* standard users. Iterate through users, send an email to each, then update the single global event's `reminder1_sent` flag.
4. **Global Reminder 2:** Same logic for global events approaching the 1h mark.

### AI Extraction Data Pipeline
1. Admin uploads `fall_calendar.pdf`.
2. Uploaded to `htdocs/EventREmainder2/uploads/fall_calendar.pdf`.
3. `ai_event_extractor.php` POSTs file to `localhost:8000/extract-events`.
4. FastAPI extracts text blocks -> Gemini LLM parses text to JSON -> `[{"title": "Midterms", "event_date": "2026-10-15 09:00:00"}]`.
5. PHP decodes JSON, executes `INSERT INTO ai_pending_events`.
6. Admin accesses `review_ai_events.php`, clicks "Approve".
7. PHP deletes from `ai_pending_events`, executes `INSERT INTO events (title, event_date, is_global) VALUES (...)`.
8. Global event is now live and will trigger reminders to all registered users.
