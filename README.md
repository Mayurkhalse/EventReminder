# 📅 Event Reminder Application

**User + Admin + AI-Assisted Academic Calendar System**

---

## 📌 Primary Objective of This Document

This `README.md` is a **high‑fidelity functional and architectural specification**.

It is intentionally written with **extra depth and precision** so that:

* 🤖 An **AI system** can use this document as full context to generate:

  * Frontend UI
  * PHP backend logic
  * SQL schema and queries
* 🎓 Interviewers can clearly evaluate:

  * System design thinking
  * Separation of concerns
  * Scalability awareness
* 🧑‍💻 A developer can implement the system **without additional clarification**

⚠️ **Out of Scope (Intentionally Excluded)**

* CRON / Task Scheduler / Python worker implementation
* Background execution configuration

This document focuses **only on**:

* Frontend behavior & UI expectations
* PHP backend responsibilities
* Database design & relationships

---

## 📖 Project Overview

The **Event Reminder Application** is a role‑based PHP web application that allows users to create time‑bound events and receive automated reminders, while giving administrators full control over **global events** and **AI‑generated academic calendar events**.

A distinguishing feature of this project is the **admin‑controlled AI pipeline**, where academic calendars (PDFs) are uploaded, parsed, and converted into structured events — **only after explicit admin approval**.

The application is designed to run on **localhost (XAMPP)** initially, but follows patterns that make it **production‑ready**.

---

## 🎯 System Goals & Design Principles

### Core Goals

* Clear separation between **UI, logic, and data**
* Strong emphasis on **automation readiness**
* Beginner‑friendly implementation with **professional architecture**
* Safe AI integration with **human‑in‑the‑loop approval**

### Design Principles

* Role‑based access control everywhere
* No business logic inside UI files
* Database‑driven state management
* Explicit workflows instead of implicit behavior

---

## 🧩 High‑Level Architecture

```
Client Browser (HTML / CSS / JS)
        ↓
PHP Controllers (Auth + Role Protected)
        ↓
Business Logic Layer
        ↓
MySQL Database
        ↓
Email Service (PHPMailer)
```

Each layer is intentionally isolated so that:

* UI changes do not affect logic
* AI logic does not bypass admin authority
* Future schedulers can be plugged in without refactoring

---

## 🎨 Frontend UI / UX Specification (Dashboard‑Style)

### UI Philosophy

The UI follows a **dashboard‑first, modern web design**, similar to internal tools used in real organizations.

### Visual & Interaction Guidelines

* Card‑based layouts for events
* Tab navigation instead of excessive pages
* Clear call‑to‑action buttons
* Modal confirmations for destructive actions
* Inline form validation (JS)
* Fully responsive layout

### UI Technology Scope

* HTML5 (semantic elements)
* CSS3 (flexbox / grid)
* Vanilla JavaScript (no frameworks)

---

## 🖥️ UI Screens & Expected Behavior

### 🔐 Login Page (`index.php`)

**Purpose:** Unified login for users and admins

**Behavior:**

* Email + password fields
* Client‑side validation
* Server‑side verification
* Redirect based on role

---

### 📝 Registration Page (`register.php`)

**Purpose:** User account creation

**Behavior:**

* Name, email, password inputs
* Password strength validation
* Duplicate email detection
* On success → redirect to login

---

### 👤 User Dashboard (`user/dashboard.php`)

**Purpose:** Central hub for user actions

**UI Sections:**

* Upcoming Events (cards)
* Past Events (tab)
* Global Events (tab)

**Actions:**

* Add new event
* Edit existing event
* Delete past events

---

### 🛠 Admin Dashboard (`admin/dashboard.php`)

**Purpose:** System‑wide control panel

**UI Sections:**

* Total users
* Total events
* Pending AI events

**Actions:**

* Create global event
* Upload academic calendar
* Review AI events

---

## 🔐 Authentication & Authorization

### Authentication Model

* PHP sessions
* Password hashing using `password_hash()`
* Verification using `password_verify()`

### Authorization Model

* Role‑based middleware

**Middleware Files:**

* `auth/auth_check.php`
* `auth/role_check.php`

These files are included at the top of every protected page.

---

## 📁 Project Structure & File Responsibilities

(Each file has a **single, clearly defined responsibility**)

```
event-reminder-app/
│
├── index.php                → Login UI
├── register.php             → Registration UI
│
├── config/
│   ├── db.php               → Database connection
│   └── mailer.php           → Email configuration
│
├── auth/
│   ├── login_action.php     → Login processing
│   ├── register_action.php  → Registration processing
│   ├── logout.php           → Session termination
│   ├── auth_check.php       → Login protection
│   └── role_check.php       → Role enforcement
│
├── user/
│   ├── dashboard.php        → User dashboard UI
│   ├── add_event.php        → Create event
│   ├── edit_event.php       → Edit event
│   ├── view_events.php      → Event listing
│   └── delete_event.php     → Event deletion
│
├── admin/
│   ├── dashboard.php        → Admin overview
│   ├── create_event.php     → Global event creation
│   ├── upload_calendar.php  → PDF upload
│   └── review_ai_events.php → AI event moderation
│
├── ai/
│   ├── pdf_parser.php       → Extract text from PDF
│   ├── ai_event_extractor.php → NLP rules
│   └── pending_events.php   → Store pending AI events
│
├── assets/
│   ├── css/style.css        → UI styles
│   └── js/script.js         → UI interactivity
│
└── database/
    └── schema.sql           → Database schema
```

---

## 🗄️ Database Design (Deep Detail)

### 👤 Users Table

Purpose:

* Stores both users and admins
* Differentiation via `role` column

Relationship:

* One user → many events

---

### 📅 Events Table

Purpose:

* Stores personal and global events

Logic:

* `user_id = NULL` → global event
* Reminder flags prevent duplication

---

### 🧠 AI Pending Events Table

Purpose:

* Temporary storage for AI‑generated events
* Enforces admin approval workflow

---

## 🔄 Detailed User Workflow

1. User registers
2. User logs in
3. User accesses dashboard
4. User creates events
5. User edits events
6. User deletes past events

At every step:

* Session validation is enforced
* Input validation is applied

---

## 🛠 Detailed Admin Workflow

1. Admin logs in
2. Admin creates global events
3. Admin uploads academic calendar PDF
4. AI extracts events
5. Events stored as pending
6. Admin reviews events
7. Approved events become global

---

## 🧠 AI Workflow (Expanded)

1. PDF uploaded
2. Text extracted
3. NLP rules applied:

   * Date detection
   * Keyword matching
   * Context grouping
4. Events inserted as pending
5. Admin approval required

AI **never bypasses admin control**.

---

## ✉️ Email System (Logic‑Only Specification)

* PHPMailer + SMTP
* Two reminders per event
* Flags updated only after successful send

⚠️ Execution trigger intentionally excluded.

---

## 🔐 Security Considerations

* Password hashing
* Session hardening
* Role enforcement
* Prepared SQL statements
* Input sanitization

---

## 🔮 Future Enhancements (Planned)

* Background scheduler integration
* Queue‑based email delivery
* Timezone handling
* SMS / WhatsApp alerts
* Analytics dashboard
* Docker & cloud deployment

---

## 🎓 Interview‑Focused Summary

> A role‑based PHP event reminder system with an admin‑controlled AI pipeline for academic calendar automation, designed with clean separation of concerns and production‑ready architecture.

---

## ✅ Final Notes

This README is deliberately verbose and explicit to act as:

* A **blueprint** for implementation
* A **prompt context** for AI code generation
* A **technical explanation** for interviews

No additional clarification should be required to begin implementation.
