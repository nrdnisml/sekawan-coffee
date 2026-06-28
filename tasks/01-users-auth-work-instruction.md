# Work Instruction: Users & Authentication Module

## Objective
Build the users and authentication module that controls who can enter the system, what role they have, and whether they are still allowed to operate the coffee shop application.

Write the implementation in a way that is easy to extend later for admin-facing user management, but keep the first delivery focused on the current business need from the ERD: valid users, valid roles, valid login state, and a clear audit trail for auth-related actions.

## Module at a Glance
- This module is the foundation for all other modules because `transactions`, `expenses`, and `activity_logs` all depend on a valid `users.id`.
- The current repo already has guest/auth routes through Volt, a `User` model, `AuthService`, and `UserService`.
- The missing business surface is an admin-facing user management page that follows the same UX style as Products Management.

## Schema Map
Primary table:
- `users`

Important columns from the current MySQL table:
- `id`
- `name`
- `username` - unique
- `email` - unique, nullable
- `password`
- `role` - currently supports the live schema values `admin`, `cashier`
- `is_active` - boolean
- `deleted_at` - soft delete support
- `remember_token`
- `created_at`, `updated_at`

ERD alignment note:
- The ERD narrative describes admin, cashier, and owner as business actors.
- The current live schema only exposes `admin` and `cashier` in `users.role`.
- If the business really needs an `owner` role in the application layer, add it through a migration and update auth/authorization tests before exposing it in the UI.

Related framework tables already present:
- `password_reset_tokens`
- `sessions`

Implementation rule:
- Use the existing `users` table as the source of truth.
- If the junior developer needs better operational fields such as `phone`, `last_login_at`, `avatar_url`, or `notes`, they may create a migration to enhance `users`.
- If the enhancement changes business behavior, it must be covered by tests and documented in the pull request.

## Current Repo Touchpoints
- Auth routes: `routes/auth.php`
- Auth views: `resources/views/livewire/auth/*.blade.php`
- User model: `app/Models/User.php`
- Login/logout logic: `app/Services/AuthService.php`
- Admin user CRUD logic: `app/Services/UserService.php`
- Existing test baseline: current auth-related feature tests plus service behavior to be expanded

## UI Guidance Aligned with Product Management
For admin user management, follow the Products Management style as the default visual system:
- Main page uses one clear header, action button, filter/search area, and one `flux:table`
- Create/Edit uses modal-based flow instead of separate pages when possible
- Status display uses badges like product active/inactive badges
- Destructive actions use a dedicated confirmation modal, not browser confirm
- Empty state, pagination, and search behavior should feel consistent with `product-list.blade.php`

Recommended admin page sections:
- Users table: name, username, email, role, active status, created date, actions
- Filter area: search by name/username/email, filter by role, filter by status
- User form modal: create/edit cashier or admin
- Confirm delete/deactivate modal

Allowed UI improvisation:
- If password reset flow or sensitive role changes need a safer UX than products, add an extra confirmation step
- If role editing should be visually separated from profile editing, it is allowed as long as the overall table + modal pattern stays consistent

## Domain Rules, Validation, and Failure Cases
Business rules to preserve:
- Only active users can log in
- Only admins can manage users
- A user cannot delete their own account
- Username must stay unique
- Email, if present, must stay unique
- Password must never be stored raw
- Soft delete is preferred over hard delete for user management

Validation and edge cases:
- Reject duplicate username/email
- Reject empty or weak password rules based on current Laravel policy used in the project
- Reject access from non-admin when entering user management routes/actions
- Reject updates that accidentally deactivate the only active admin if that scenario becomes possible after enhancement

## Implementation Slices
### Slice 1 - Stabilize auth behavior against current schema
- Confirm login uses `username` + `password`
- Confirm inactive account is blocked cleanly
- Confirm logout still writes audit logs

### Slice 2 - Add admin user management screen
- Create Livewire module under `app/Livewire/Users/**`
- Create matching Blade views under `resources/views/livewire/users/**`
- Register protected route in `routes/web.php`
- Keep page behavior aligned to product module table and modal flow

### Slice 3 - Add create/edit/deactivate actions
- Reuse `UserService` for business rules
- Keep authorization in both UI layer and service layer
- Prefer deactivate/soft delete over permanent removal in UI

### Slice 4 - Add filters and operational polish
- Search, role filter, status filter, pagination
- Clear badges for admin/cashier and active/inactive
- Useful empty states and success/error toasts

## TDD Plan
Follow RED -> GREEN -> REFACTOR.

Write tests before production changes in this order:
1. Authentication success and failure paths
2. Admin authorization for user management actions
3. Create user
4. Update user
5. Deactivate/delete user
6. List/filter/search users in Livewire page

Suggested test files:
- `tests/Feature/AuthFlowTest.php`
- `tests/Feature/UserManagementTest.php`
- `tests/Unit/Services/AuthServiceTest.php`
- `tests/Unit/Services/UserServiceTest.php`

## Production-Grade Unit Testing Mechanism
Use a mixed strategy, not only one test style.

### 1. Service-level tests
Target:
- `AuthService`
- `UserService`

Must verify:
- Login succeeds with valid active user
- Login fails for wrong password
- Login fails for inactive user
- Logout writes expected audit trail
- Admin can create/update/delete or deactivate users
- Non-admin is rejected
- Self-delete is rejected
- Password is stored hashed

### 2. Feature/HTTP tests
Must verify:
- Guest can access intended auth pages only
- Protected routes redirect guests
- Authenticated admin can access user management route
- Cashier cannot access admin-only user management route

### 3. Livewire component tests
If user management page is Livewire-based, verify:
- Table renders correct users
- Search/filter works
- Form validation errors show correctly
- Modal create/edit flow dispatches success event and refreshes data

### 4. Database assertions
Use assertions for:
- row inserted into `users`
- `is_active` changed correctly
- soft delete applied when required
- optional audit row inserted into `activity_logs`

### 5. Regression coverage
Add regression tests for:
- duplicate username/email
- admin-only restrictions
- user deactivated after being previously valid

Production-grade expectation:
- Use `RefreshDatabase`
- Use factories, not hand-built fixtures unless needed for edge cases
- Cover both happy path and forbidden path
- Assert side effects in DB, not only UI messages

## Acceptance Checklist
- User/auth instructions are grounded in the live `users` table
- Admin management UI guidance follows the products module style
- The document clearly separates what already exists from what needs implementation
- The document allows schema enhancement when justified
- The testing mechanism is concrete enough for a junior developer to execute without guessing
