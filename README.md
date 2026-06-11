<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="public/assets/images/logo_alt.png">
    <source media="(prefers-color-scheme: light)" srcset="public/assets/images/logo.png">
    <img alt="Nova Helpdesk Logo" src="public/assets/images/logo.png" width="320">
  </picture>
</p>

<p align="center">
  <a href="https://github.com/tomylana93/nova-helpdesk/actions/workflows/tests.yml"><img src="https://github.com/tomylana93/nova-helpdesk/actions/workflows/tests.yml/badge.svg" alt="Tests Status"></a>
  <img src="https://img.shields.io/badge/PHP-%3E%3D_8.5-777bb4.svg?style=flat-square&logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/Laravel-13-ff2d20.svg?style=flat-square&logo=laravel" alt="Laravel Version">
  <img src="https://img.shields.io/badge/Vue-3-4fc08d.svg?style=flat-square&logo=vue.js" alt="Vue Version">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-38bdf8.svg?style=flat-square&logo=tailwindcss" alt="Tailwind CSS Version">
  <img src="https://img.shields.io/badge/Tests-Pest_PHP-00b4b6.svg?style=flat-square&logo=pest" alt="Pest PHP Version">
  <img src="https://img.shields.io/badge/Code_Style-Laravel_Pint-000000.svg?style=flat-square" alt="Laravel Pint">
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=flat-square" alt="License">
</p>

# Nova Helpdesk

Nova Helpdesk is a modern, single-company internal IT Helpdesk system designed to manage and resolve support tickets (incidents and service requests) across multiple company branches. The platform is built on Laravel 13, Fortify, and Inertia Vue 3, featuring a responsive, state-of-the-art user interface styled with Tailwind CSS v4.

---

## 🚀 Key Features

- **Ticketing Core & Lifecycle**: Supports Incidents and Service Requests. A strict backend state machine (`TicketStatus`) governs ticket transitions (`Open`, `PendingApproval`, `InProgress`, `WaitingForRequester`, `Resolved`, `Closed`, `Reopened`).
- **Service Request Approvals**: Service requests enter a `PendingApproval` state where the assigned IT Agent (not the super admin) must approve or reject the request before work begins.
- **SLA Policies & Calculations**: Auto-calculates first response and resolution due timestamps on ticket creation based on ticket type and priority (`Low`, `Medium`, `High`, `Urgent`).
- **Personalized Notifications**: Direct updates delivered to the user's personal channel via Reverb websockets and database notifications (auto-assigned alerts, SLA warnings/breaches, replies).
- **Master Data Management**: Super Admin panel to manage users, branches, departments, ticket categories, and SLA policies.
- **Branded Theme**: Fully dynamic layout variables, dark mode preference cookies, custom site name database settings (falling back to "Nova Helpdesk"), and typography.
- **Semantic Versioning (SemVer)**: Automatic release version tracking inside `version.json` with an automated commit/tag versioning command.

---

## 🛠️ Technology Stack

- **Backend**:
    - PHP 8.5 & Laravel 13
    - Laravel Fortify (Authentication backend)
    - Inertia.js (Inertia Laravel v3)
    - Spatie Laravel Settings (Branding & General Settings persistence)
    - Spatie Laravel Permission (Roles & Permissions catalog)
    - Spatie Laravel MediaLibrary (Avatar & Upload attachments)
    - Spatie Laravel Query Builder (API filtering & server-driven tables)
- **Frontend**:
    - Vue 3 & TypeScript
    - Tailwind CSS v4 (with Vite integration)
    - Reka UI & Lucide Vue Icons
    - Laravel Wayfinder (Auto-generated typed Laravel routes in Vue)
- **Testing & Quality Assurance**:
    - Pest PHP v4
    - PHPStan (Larastan v3)
    - Rector (Automatic refactoring)
    - Laravel Pint (PHP formatting)
    - Prettier & ESLint (Frontend styling and lint checks)

---

## 💻 Getting Started

### Prerequisites

- PHP 8.5 or higher
- Node.js & `pnpm`
- SQLite (default database connection)

### Installation & Setup

Set up your database, install dependencies, run migrations, and compile assets in one command:

```bash
composer run setup
```

### Running the Local Development Stack

Start the Laravel local server, queue listener, Vite dev server, and Reverb websocket server concurrently:

```bash
composer run dev
```

### Bumping Application Version

To bump the version using SemVer, run the Artisan command (by default it will automatically determine the bump type based on git commit history):

```bash
# Auto mode (calculates bump type from Conventional Commit history)
composer run version:bump

# Or manually specify the bump type
composer run version:bump -- patch
composer run version:bump -- minor
composer run version:bump -- major
```

---

## 👥 Personas & Roles

| Role                      | Ticket Lifecycle                                                                                                                | System & Master Data                                                                    |
| :------------------------ | :------------------------------------------------------------------------------------------------------------------------------ | :-------------------------------------------------------------------------------------- |
| **`requester`**           | Create tickets, track progress, add replies, confirm resolution, reopen tickets. _Must belong to a branch and department._      | None (Read-only portal)                                                                 |
| **`it_agent`**            | Approve/reject service requests, start work, resolve tickets, add public/internal notes.                                        | None (Read-only master data)                                                            |
| \***\*`super_admin`\*\*** | Read-only oversight of the ticket database. Excluded from the ticketing lifecycle (no assignment, approvals, or notifications). | Full management of system settings, style, and master data (users, branches, SLA, etc.) |

---

## 🧪 Testing & Quality Gate

Verify code quality, formatting, typescript types, static analysis, and execute the test suite before committing:

```bash
# Run Pint, Prettier, TypeScript compile, PHPStan, Rector dry-run, and Pest test suite
composer run ci:check

# Run tests only
composer test

# Run a focused test
php artisan test --compact --filter=TicketLifecycleTest
```
