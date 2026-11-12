# SinhVienMarket KTX Platform Foundation

## Architecture decision

- Keep the existing PHP MVC application as the modular-monolith backend. Do not introduce Laravel or a second API server.
- Keep Next.js as the web frontend. It calls the PHP JSON API with the existing HTTP-only session cookie and CSRF token.
- Keep MySQL as the current database. KTX tables are introduced by additive migrations; `database/schema.sql` is a destructive fresh-install/reset script and must not be used to upgrade an existing database.
- Keep the marketplace operational as a separate business module. KTX contracts and fees must not be conflated with marketplace transactions.
- Keep AI outside the first KTX delivery. AI Matching remains part of the marketplace roadmap; management assistance is a later optional service.

## Current V1 audit

| Area | Existing state | Consequence |
|---|---|---|
| Backend | PHP 8.1+ MVC, PDO, MySQL, one front controller and explicit routes | Add domain models/controllers and JSON routes using the current conventions |
| Frontend | Next.js App Router, TypeScript, mock/real API adapter | Add student and dorm-management screens without restoring PHP page templates |
| Identity | `users` stores marketplace accounts; its legacy `role` enum is `student`/`admin`; session, OTP, lock, and CSRF flows exist | Additive RBAC tables extend authorization without breaking existing marketplace role checks |
| Student data | Marketplace profile fields are in `users`; no residence profile, application, or contract entities exist | Add a dedicated student profile and residence records |
| Authorization | `Middleware` primarily distinguishes authenticated/admin users; JSON APIs have student-specific guards | Enforce granular KTX permissions inside the new API controller, not in the browser |
| Persistence | `scripts/migrate.php` runs sorted SQL/PHP migrations and records applied files | Use a forward-only migration; do not edit the existing destructive schema to upgrade |
| GitHub | `origin` points to `katosama0902-pixel/sinhvien-market`; current feature branch tracks the frontend branch | Continue on the current branch; do not push or change branch ownership without an explicit request |
| Local tooling | PHP CLI exists, but local `pdo_mysql`, PHPUnit, and Docker are unavailable | PHP lint and frontend checks can run here; database migration/transaction tests require the Docker/MySQL environment |

The existing session-backed APIs, Next.js frontend and marketplace database are useful foundations. The principal V1 gap for this scope is domain data and authorization—not a need to replace the backend.

## MVP workflows

### Student residence application

1. A registered student completes their student profile (student number and university are required to apply).
2. The student submits a requested stay period, preferred room category, and optional note.
3. The application starts as `submitted`; overlapping active applications for the same student are rejected.
4. A dormitory manager moves it to `under_review`, then approves or rejects it with a recorded decision note.
5. Approval atomically creates one residence contract in `pending_assignment`. Room/bed assignment and contract activation are deliberately deferred until the room inventory module is implemented.
6. The student can view their application history and contract status.

### Identity and roles

- Existing `student` and `admin` login behavior remains intact.
- Effective roles are additive: the legacy role supplies the default student/admin identity, while `user_roles` grants additional roles such as `dorm_manager`, `service_staff`, or `tenant`.
- Admin remains a superuser for existing administration. A future least-privilege admin model can replace this compatibility rule.
- The backend checks permission keys on every protected KTX API. Hiding a link or button is not authorization.

## Roles and initial permission matrix

| Capability | Student | Dorm manager | Service staff | Tenant/partner | Admin |
|---|---:|---:|---:|---:|---:|
| Read/update own student profile | Yes | No | No | No | Yes |
| Submit/read own residence application | Yes | No | No | No | Yes |
| Review residence applications | No | Yes | No | No | Yes |
| Read residence contracts | Own | Assigned scope later | No | No | Yes |
| Service requests | Create/own status later | Oversight later | Assigned work later | No | Yes |
| Commercial spaces/contracts | Browse later | Manage later | No | Own later | Yes |
| Marketplace | Existing student flow | Existing account behavior | Existing account behavior | Existing account behavior | Existing admin flow |
| Assign system roles | No | No | No | No | Yes |

Scope filtering by building, floor, or service team is not implemented yet; current dorm permissions are platform-wide and must be narrowed before real-world deployment.

## Use cases

- **UC-01 — Sign in:** authenticate through the existing PHP-backed session and OTP flow; return the effective role list.
- **UC-02 — Maintain student profile:** a student reads/updates student number, university, faculty, program, academic year, date of birth, and emergency contact.
- **UC-03 — Submit residence application:** a student with a complete profile submits a non-overlapping requested stay period.
- **UC-04 — Review application:** a dorm manager reads applications, starts review, and records an approval/rejection decision.
- **UC-05 — Create/view contract:** approval creates a single `pending_assignment` contract; the student and authorized manager can view it.
- **UC-06 — Assign a role:** an admin grants a non-admin KTX role to an existing account.

## Conceptual ERD

```text
users 1──0..1 student_profiles
users 1──* user_roles *──1 roles 1──* role_permissions *──1 permissions
users 1──* residence_applications
residence_applications 1──* residence_application_events
residence_applications 1──0..1 residence_contracts
users 1──* residence_application_events (actor)
```

`residence_contracts.application_id` is unique so one approval cannot create duplicate contracts. Every review transition has an actor, timestamp, and note in the event history. Building/room/bed assignment is intentionally not fabricated in this phase.

## API contract

All endpoints use the existing `{ success, data, message }` success envelope and `{ success: false, error: { code, message } }` error envelope. Mutations require the existing session CSRF token (`X-CSRF-Token`) and return JSON. IDs are integers, dates are `YYYY-MM-DD`, and enum values are stable lowercase identifiers.

| Method and path | Permission | Request / response |
|---|---|---|
| `GET /api/auth/me` | Authenticated | Existing identity plus effective `roles[]` |
| `GET /api/student/profile` | `student.profile.read` | Student profile fields |
| `PUT /api/student/profile` | `student.profile.update` | Student profile fields; returns saved profile |
| `GET /api/residence/applications` | `residence.application.read` | Current student's applications |
| `POST /api/residence/applications` | `residence.application.create` | `{ startDate, endDate, preferredRoomType, note }`; returns submitted application |
| `GET /api/residence/contracts` | `residence.contract.read` | Current student's contracts |
| `GET /api/admin/residence/applications` | `residence.application.review` | Applications for authorized managers |
| `POST /api/admin/residence/applications/{id}/review` | `residence.application.review` | `{ decision: "under_review" \| "approve" \| "reject", note? }` |
| `GET /api/admin/residence/contracts` | `residence.contract.manage` | Contracts for authorized managers |
| `GET /api/admin/roles` | `system.roles.manage` | Assignable KTX role keys |
| `POST /api/admin/users/{id}/roles` | `system.roles.manage` | `{ roleKey }`; only non-admin KTX roles may be assigned |

Important errors include `NOT_AUTHENTICATED` (401), `FORBIDDEN` (403), `VALIDATION_ERROR` (422), `PROFILE_INCOMPLETE` (409), `APPLICATION_OVERLAP` (409), `INVALID_STATE_TRANSITION` (409), and `NOT_FOUND` (404).

## Weeks 5–8 — inventory, accommodation, service desk, and rentals

The additive `20261104_ktx_operations.sql` migration introduces building → floor → room → bed inventory, bed allocation/event history, service requests, rentable spaces, rental applications, and rental contracts. Apply it with the normal migration runner only after the KTX foundation migration; do not import the destructive fresh-install schema into an existing database.

### Operational rules

- Dorm managers maintain inventory through `POST /api/admin/residence/{buildings|floors|rooms|beds}`, `PUT/DELETE /api/admin/residence/{resource}/{id}`. Foreign-key references prevent deleting in-use inventory; room capacity cannot be lowered below its bed count.
- Approved residence contracts remain `pending_assignment` until a manager assigns a bed. Assignment locks the contract, bed, and student records and rejects overlapping bed or student schedules. A transfer closes the previous allocation the day before the effective date, opens a new allocation, and records the actor and both bed IDs.
- Students can read their own accommodation history at `GET /api/residence/accommodation/history`. Managers can see pending contracts and available beds at `/api/admin/residence/allocations` and `/api/admin/residence/available-beds`; allocate with `POST /api/admin/residence/allocations` and transfer with `POST /api/admin/residence/allocations/{id}/transfer`.
- Authenticated students submit service, repair, and feedback tickets at `POST /api/residence/service-requests`; users only see their own tickets unless they have the service-management permission. Service staff and dorm managers can assign a service-staff account and update the status/note with `PUT /api/admin/residence/service-requests/{id}`.
- Tenants browse `/api/rental/spaces`, submit and view their own applications at `/api/rental/applications`, and read their contracts at `/api/rental/contracts`. Dorm managers review `/api/admin/rental/applications`; approval creates one rental contract in the same transaction. Managers create/update/delete spaces at `/api/admin/rental/spaces`.
- Every write uses the existing session and CSRF flow. Permission checks run in the PHP API; the Next.js role gates are only user-interface hints. Schedule and rental-overlap errors return conflict responses; CRUD deletes are restricted when foreign keys show the record is in use.

### Frontend

- `/admin/residence/operations` provides inventory CRUD, residence-bed assignment and transfers, service ticket handling, rental-space management, and rental-application/contract review.
- `/residence/services` provides ticket submission and tracking; service staff see the work queue and can update progress. Managers can also view accommodation history.
- `/rentals` provides available-space browsing, applications, and contract status for authorized accounts.

The API persists data in MySQL and uses the existing `apiRequest` cookie/CSRF client. No migration is run automatically by these changes. (404).

## Development setup

The repository already has a GitHub remote and the Next.js app. A local Docker Compose setup is provided in `docker-compose.yml`; Docker must be installed to run it. After starting the services, initialize a new empty MySQL database once with `database/schema.sql`, then apply later changes with `php scripts/migrate.php`. Never import the destructive schema into an existing database.

Typical commands:

```powershell
docker compose up --build
Get-Content .\database\schema.sql -Raw | docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
docker compose exec php php scripts/migrate.php
```

The PHP API is exposed at `http://localhost:8080`; Next.js is exposed at `http://localhost:3000`. Local-only database credentials in Compose are for development and must not be reused in production.

## Delivery boundaries

This iteration implements identity/permissions, student profile, residence application submission/review, contract creation/read, and the first student/admin screens. Room inventory and bed allocation, contract signature, fees/payments, service requests, rental workflows, notifications, and AI are later modules.
