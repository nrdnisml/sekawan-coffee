# Work Instruction: Audit & Activity Logs Module

## Objective
Build the audit and activity log module as a reliable read-first operational record for the system. The main goal is not editing logs, but making sure important actions from other modules are recorded and can be reviewed clearly.

This module should help admin users answer simple operational questions such as: who changed a price, who created a transaction, who deleted a user, and when it happened.

## Module at a Glance
- This is a cross-cutting module, not an isolated CRUD module.
- The current repo already has `activity_logs` table support, `ActivityLog` model, and `AuditService`.
- Several services already call `AuditService`, but there is no dedicated UI page yet.
- The first implementation should prioritize read, filter, and traceability rather than create/edit/delete screens.

## Schema Map
Primary table:
- `activity_logs`

Important columns from the current MySQL table:
- `id`
- `user_id` - nullable FK to `users.id`
- `action`
- `entity`
- `entity_id`
- `description`
- `created_at`

Current relational meaning:
- `user_id` tells who performed the action when available
- `entity` stores the affected domain, for example `products`, `transactions`, `users`, or `expenses`
- `entity_id` stores the affected record id when relevant

Enhancement policy:
- If filtering needs become too limited, the junior developer may add columns such as `metadata` JSON, `ip_address`, or `context`
- If detailed diff tracking becomes necessary, a new table may be introduced, but only if the simpler `activity_logs` enhancement is not enough

## Current Repo Touchpoints
- Model: `app/Models/ActivityLog.php`
- Service: `app/Services/AuditService.php`
- Current log producers include:
  - `AuthService`
  - `UserService`
  - `ProductService`
  - `ExpenseService`
  - `TransactionService`

## UI Guidance Aligned with Product Management
Follow the product module visual language, but adapt it for a read-only audit surface:
- Use a table-first page with filters at the top
- Use badges for action types or entity types when useful
- Use a detail modal or drawer for long descriptions or structured metadata
- No inline edit form is needed for logs
- Keep pagination, empty state, and filter behavior consistent with the products table

Recommended screen sections:
- Audit table: timestamp, actor, action, entity, entity id, short description
- Filter area: date range, actor, action, entity
- Optional detail modal: full description and related record links

Allowed improvisation:
- If a timeline layout helps readability for a single record detail view, it is allowed
- If the business later needs export for admin reporting, document it as a later enhancement, not part of the first slice unless explicitly requested

## Domain Rules, Validation, and Failure Cases
Business rules:
- Audit rows should be append-only in normal operation
- Logs must not be editable from the UI
- Missing user should not break log readability because `user_id` is nullable
- Logging failure must never block the main business transaction unexpectedly unless the project intentionally changes that policy

Failure cases to handle:
- Null actor for system/background events
- Missing related record because entity was deleted later
- Long descriptions that need truncation in table view but full visibility in detail view

## Implementation Slices
### Slice 1 - Formalize log viewing surface
- Add audit listing route and Livewire page
- Show searchable/filterable table
- Show useful empty state and pagination

### Slice 2 - Improve readability
- Add badges for `action` and/or `entity`
- Add detail modal for long descriptions
- Show actor name with graceful fallback when user is null or deleted

### Slice 3 - Harden cross-module expectations
- Review all major services and confirm meaningful descriptions are being written
- If some services log too little context, improve the message format

## TDD Plan
Follow RED -> GREEN -> REFACTOR.

Write tests before production changes in this order:
1. `AuditService` creates log row
2. Log creation still fails safely when DB insert throws inside audit layer
3. Audit list page renders rows correctly
4. Filters work by actor/action/entity/date
5. Cross-module services create expected audit rows

Suggested test files:
- `tests/Unit/Services/AuditServiceTest.php`
- `tests/Feature/ActivityLogManagementTest.php`

## Production-Grade Unit Testing Mechanism
### 1. Service-level tests
Must verify:
- `AuditService::log()` inserts the expected record
- nullable `user_id` is supported
- service does not crash the caller contract when audit persistence fails

### 2. Feature/Livewire tests
Must verify:
- audit page is protected correctly
- admin/authorized role can view audit listing
- rows appear in descending time order by default
- filters narrow results correctly
- detail modal or detail section shows full description

### 3. Cross-module integration assertions
Create focused tests that prove logs are written when:
- user logs in or logs out
- user is created/updated/deleted
- expense is created
- transaction is created/cancelled/refunded

### 4. Database assertions
Assert directly against:
- `activity_logs.action`
- `activity_logs.entity`
- `activity_logs.entity_id`
- `activity_logs.user_id`
- `activity_logs.description`

Production-grade expectation:
- Treat audit as part of observable business behavior
- Verify actual persisted rows, not only mocked method calls
- Include edge cases with deleted or null actors

## Acceptance Checklist
- The document treats audit as a read-first module, not a normal CRUD form
- The instructions are grounded in the current `activity_logs` table and `AuditService`
- The UI guidance stays visually consistent with products management
- The document explicitly allows safe enhancement when filtering/detail needs exceed the current schema
- The testing mechanism covers both standalone audit logic and cross-module log creation
