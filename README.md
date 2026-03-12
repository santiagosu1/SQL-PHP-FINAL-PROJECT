# TicketAPI — Secure PHP + MySQL Backend

A RESTful backend API for an event ticketing platform built with PHP and MySQL. Supports user authentication, role-based authorization, ticket purchasing, and event management.

---

## Tech Stack

- **Backend:** PHP 8+ (procedural + OOP)
- **Database:** MySQL 8
- **Server:** AMPPS (Apache + PHP + MySQL)
- **Testing:** Postman

---

## Project Structure

```
SQL-PHP-FINAL-PROJECT/
├── auth/
│   ├── login.php          # POST   — login, creates session
│   ├── logout.php         # POST   — destroys session
│   └── register.php       # POST   — register new user (role: user)
├── config/
│   └── database.php       # Database class (OOP, PDO connection)
├── events/
│   ├── create.php         # POST   — create event (protected)
│   ├── delete.php         # DELETE — delete event (admin only)
│   ├── list.php           # GET    — list all events
│   └── update.php         # PUT    — update event (owner or admin)
├── tickets/
│   ├── create.php         # POST   — purchase ticket (protected)
│   ├── delete.php         # DELETE — cancel ticket (admin only)
│   └── list.php           # GET    — list tickets
└── sql/
    ├── schema.sql          # Table definitions
    ├── data.sql            # Seed data
    └── exported_database.sql
```

---

## Database Setup

### 1. Requirements
- MySQL 8 running locally (AMPPS, XAMPP, or similar)

### 2. Import the database

**Option A — using the schema + data files:**
```bash
mysql -u root -p < sql/schema.sql
mysql -u root -p < sql/data.sql
```

**Option B — using the exported dump:**
```bash
mysql -u root -p < sql/exported_database.sql
```

### 3. Database configuration

Edit `config/database.php` if your credentials differ:
```php
private string $host     = 'localhost';
private string $dbname   = 'ticket_api';
private string $username = 'root';
private string $password = 'mysql';
```

---

## Database Schema

### `users`
| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT | PK |
| email | VARCHAR(150) | UNIQUE, NOT NULL |
| password | VARCHAR(255) | bcrypt hashed |
| first_name | VARCHAR(100) | NOT NULL |
| last_name | VARCHAR(100) | NOT NULL |
| role | ENUM('admin','user') | default: user |
| created_at | TIMESTAMP | auto |

### `events`
| Column | Type | Notes |
|---|---|---|
| id | VARCHAR(20) | PK (e.g. evt-12345) |
| title | VARCHAR(200) | NOT NULL |
| event_date | DATE | NOT NULL |
| created_by | INT | FK → users.id |
| ... | ... | price, venue, category, tickets |

### `tickets`
| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT | PK |
| user_id | INT | FK → users.id |
| event_id | VARCHAR(20) | FK → events.id |
| quantity | INT | NOT NULL |
| total_price | DECIMAL(10,2) | NOT NULL |
| purchase_date | TIMESTAMP | auto |

### `audit_logs`
| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT | PK |
| user_id | INT | FK → users.id |
| action | VARCHAR(20) | CREATE / UPDATE / DELETE |
| entity | VARCHAR(50) | e.g. events, tickets |
| entity_id | VARCHAR(50) | affected record ID |
| timestamp | TIMESTAMP | auto |

---

## API Endpoints

### Auth

| Method | Endpoint | Body | Auth |
|---|---|---|---|
| POST | `/auth/register.php` | `email, password, first_name, last_name` | Public |
| POST | `/auth/login.php` | `email, password` | Public |
| POST | `/auth/logout.php` | — | Session required |

### Events

| Method | Endpoint | Notes | Auth |
|---|---|---|---|
| GET | `/events/list.php` | List all events | Public |
| POST | `/events/create.php` | Create event | Login required |
| PUT | `/events/update.php` | Update event | Owner or admin |
| DELETE | `/events/delete.php` | Delete event | Admin only |

### Tickets

| Method | Endpoint | Notes | Auth |
|---|---|---|---|
| GET | `/tickets/list.php` | All tickets or `?user_id=X` | Public |
| POST | `/tickets/create.php` | Buy ticket | Login required |
| DELETE | `/tickets/delete.php` | Cancel ticket | Admin only |

---

## JSON Response Format

**Success:**
```json
{
  "success": true,
  "data": [],
  "message": "Operation completed."
}
```

**Error:**
```json
{
  "success": false,
  "error": "Validation failed."
}
```

---

## Authentication & Authorization

- Passwords hashed with `password_hash()` (bcrypt)
- Login stores `user_id` and `role` in `$_SESSION`
- Protected endpoints check `$_SESSION['user_id']` before processing
- Two roles: **admin** and **user**
  - `admin` — can DELETE events and tickets, can update any event
  - `user` — can only update events they created (`created_by`)

---

## Testing with Postman

### 1. Register
```
POST http://localhost/SQL-PHP-FINAL-PROJECT/auth/register.php
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "secret123",
  "first_name": "John",
  "last_name": "Doe"
}
```

### 2. Login
```
POST http://localhost/SQL-PHP-FINAL-PROJECT/auth/login.php
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "secret123"
}
```
> Postman automatically stores the session cookie for subsequent requests.

### 3. Buy a ticket
```
POST http://localhost/SQL-PHP-FINAL-PROJECT/tickets/create.php
Content-Type: application/json

{
  "event_id": "evt-001",
  "quantity": 2
}
```

### 4. Delete an event (admin only)
```
DELETE http://localhost/SQL-PHP-FINAL-PROJECT/events/delete.php
Content-Type: application/json

{
  "id": "evt-001"
}
```

---

## Security Measures

- All SQL uses **prepared statements** (PDO) — no raw input in queries
- Emails validated with `filter_var(FILTER_VALIDATE_EMAIL)`
- Strings sanitized with `htmlspecialchars()`
- Numeric inputs cast with `(int)` / `(float)`
- `user_id` always taken from `$_SESSION`, never from request body
- `session_regenerate_id(true)` on login to prevent session fixation

---


