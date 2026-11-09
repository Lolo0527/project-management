# Planova - Project Management System (Company Portal)

A complete, role-based Project Management System built with **Laravel 12** and **PicoCSS**. Planova enables teams to manage company projects, assign tasks, track progress on a live Kanban board, collaborate through task discussions with file attachments, receive smart notifications, generate productivity reports, and manage the full project review and completion lifecycle.

---

## Key Features

1. **Role-Based Access Control (RBAC)**
   - **Admin**: Full access across all modules, register and edit employees, assign roles, manage system permissions.
   - **Manager**: Create and manage projects, assign tasks, review submitted work, request revisions, approve tasks, and close completed projects.
   - **Employee**: Dedicated employee dashboard, view assigned projects and tasks, move tasks on Kanban, submit completed work for review, comment, and upload deliverables.

2. **Manager Dashboard**
   - Live metrics: Total Employees, Total Projects, Active Projects, Completed Projects, Pending Tasks, Overdue Tasks.
   - Quick overview of Upcoming Deadlines and Employee Productivity statistics.

3. **Employee Management**
   - Create, edit, and deactivate employee accounts.
   - Department assignment and automated credential generation.
   - Role escalation prevention (only administrators can create/modify admin accounts).

4. **Project Management**
   - Create and edit projects with start dates, deadlines, managers, team members, priorities, and statuses.
   - Deadline updates with instant notifications to team members.

5. **Task Management**
   - Connect tasks to projects and assign employees.
   - Priority levels (`low`, `medium`, `high`), start dates, due dates, statuses (`pending`, `in_progress`, `completed`), and file attachments.

6. **Interactive Kanban Board**
   - 3 Column Workflow: **To Do**, **In Progress**, and **Completed**.
   - Drag-and-drop card movement and quick status dropdowns.
   - **Role Scoped**: Employees only see and modify their assigned tasks; managers see tasks within their projects.

7. **Task Comments & File Attachments**
   - In-task communication threads with support for PDF, image, and document attachments.
   - In-line image preview and download links.
   - Access control preventing unauthorized users from accessing private project threads.

8. **Smart Notification Center**
   - Automated notifications for:
     - New task assigned
     - New comment posted in discussion
     - Task status changed
     - Approaching task deadlines
     - Project deadline changed
     - Manager approves submitted work
   - Unread badges, mark single as read, and mark all as read.

9. **Productivity Reports**
   - Company-wide metrics on completed, pending, and overdue tasks.
   - Employee workload breakdown with completion rate progress indicators.
   - Project completion percentage tracking based on approved deliverables.

10. **Project Review & Completion Lifecycle**
    - Employee finishes task $\to$ Submits work with deliverable/notes for review.
    - Manager reviews submitted deliverables.
    - Manager approves work ($\to$ `is_approved = true`) or requests revisions ($\to$ returns task to `in_progress` with feedback).
    - Once all tasks are approved, the manager can close the project ($\to$ `status = completed`).

---

## Getting Started

### Prerequisites
- PHP >= 8.2 (with SQLite / PDO extension)
- Composer

### Installation

1. **Clone the repository:**
   ```bash
   git clone <your-repo-url>
   cd company-portal
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Prepare Database & Run Migrations with Seeders:**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

5. **Create Storage Symbolic Link:**
   ```bash
   php artisan storage:link
   ```

6. **Start Local Development Server:**
   ```bash
   php artisan serve
   ```
   Open `http://127.0.0.1:8000` in your web browser.

---

## Seeded Default Accounts

All default seeded accounts use password: `password123`

| Role | Name | Email |
|------|------|-------|
| **Admin** | Administrator | `admin@company.com` |
| **Manager** | Project Manager | `manager@company.com` |
| **Employee** | Jane Doe | `jane@company.com` |
| **Employee** | John Smith | `john@company.com` |
| **Employee** | Alex Brown | `alex@company.com` |
| **Employee** | Sarah Wilson | `sarah@company.com` |
| **Employee** | James Miller | `james@company.com` |
| **Employee** | Peter Davis | `peter@company.com` |

---

## Running Automated Tests

To run the complete verification suite covering all 10 tasks:
```bash
php tests/test_planova_complete.php
```

To run deadline reminder checks:
```bash
php artisan tasks:check-deadlines
```
