# Work Instruction: Expenses Module

## Objective
Build the expenses module so operational spending can be recorded, listed, filtered, and reviewed with proper attribution to the user who entered the expense.

This module is financially important because profit cannot be understood from sales alone. Keep the first version simple and reliable: record expense, show expense history, and make the data easy to audit.

## Module at a Glance
- The current repo already has the schema, model, and service for expenses
- There is no dedicated route, Livewire page, or Blade UI yet
- The module should follow the product-management interaction style, but with finance-friendly columns and filters

## Schema Map
Primary table:
- `expenses`

Important columns from the current MySQL table:
- `id`
- `user_id` - FK to `users.id`
- `description`
- `amount`
- `expense_date`
- `created_at`, `updated_at`

Current meaning:
- Every expense must be attributable to the user who recorded it
- `description` is the operational explanation
- `amount` is the monetary value
- `expense_date` is the business date of the spending event

Enhancement policy:
- If reporting quality requires categorization, the junior developer may add columns such as `category`, `reference_number`, `attachment_path`, or `notes`
- If category management becomes large enough, a new `expense_categories` table may be created

## Current Repo Touchpoints
- Model: `app/Models/Expense.php`
- Service: `app/Services/ExpenseService.php`
- Related actor table: `users`
- Related cross-cutting behavior: `AuditService`
- Current feature reference only: `tasks/list-features.md`

## UI Guidance Aligned with Product Management
Follow the product-management UI style as the default pattern:
- One main page with title, filters, and `flux:table`
- Create/edit through modal-based forms when practical
- Clear empty state and pagination
- Confirmation modal for destructive actions if delete is allowed
- Consistent toast feedback after save/update

Recommended expense page sections:
- Expense table: date, description, amount, recorded by, optional category, actions
- Filter area: search description, filter date range, filter recorded-by user
- Expense form modal: add/edit expense
- Optional detail modal if attachments or long notes are introduced later

Allowed improvisation:
- Amount column may use stronger emphasis styling than products because finance values need quicker scanning
- A summary strip for total expense in the current filter range is allowed if it remains lightweight

## Domain Rules, Validation, and Failure Cases
Business rules:
- Expense must belong to a valid user
- Amount must be positive
- Expense date must be clear and filterable
- Description must be readable enough for audit purposes
- Expense changes should write meaningful audit logs

Failure cases to handle:
- zero or negative amount
- missing description
- invalid date range filter
- unauthorized access to expense management page/actions

## Implementation Slices
### Slice 1 - Add expense listing page
- Create Livewire component and route
- Render filterable table
- Add pagination and empty state

### Slice 2 - Add create flow
- Modal-based form aligned with products pattern
- Reuse `ExpenseService` for creation logic
- First delivery scope is fixed to list + create only
- Do not include edit/delete in the first delivery unless explicitly requested later

### Slice 3 - Add filtering and operational polish
- Date range filter
- User filter
- Search description
- Currency formatting and readable date formatting

## TDD Plan
Follow RED -> GREEN -> REFACTOR.

Write tests before production changes in this order:
1. expense creation through `ExpenseService`
2. date/user filtering through service or query layer
3. protected route access
4. Livewire list rendering
5. Livewire create form validation
6. audit trail assertion after create

Suggested test files:
- `tests/Unit/Services/ExpenseServiceTest.php`
- `tests/Feature/ExpenseManagementTest.php`

## Production-Grade Unit Testing Mechanism
### 1. Service-level tests
Must verify:
- expense is created with correct `user_id`, `description`, `amount`, `expense_date`
- filtering by date range works
- filtering by user works

### 2. Feature/Livewire tests
Must verify:
- authorized user can access expense page
- unauthorized or guest user is blocked according to chosen policy
- table renders seeded expenses correctly
- search/date filters work together
- form validation errors appear for invalid amount/description/date

### 3. Audit assertions
Must verify:
- creating an expense writes a meaningful `activity_logs` row

### 4. Database assertions
Assert directly against:
- `expenses.user_id`
- `expenses.description`
- `expenses.amount`
- `expenses.expense_date`
- corresponding `activity_logs` row when relevant

Production-grade expectation:
- Cover money-related edge cases carefully
- Prefer decimal-safe assertions matching stored values
- Verify both the operational record and the audit side effect

## Acceptance Checklist
- The document is grounded in the real `expenses` table and `ExpenseService`
- The UI guidance matches the products module style without inventing a new visual language
- The document allows enhancement of the expense schema when justified
- The testing mechanism is detailed enough to guide implementation confidently
- The module stays focused on operational spending, not generic accounting overreach
