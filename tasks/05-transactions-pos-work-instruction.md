# Work Instruction: Transactions & POS Module

## Objective
Build the transactions and POS module that allows cashier users to complete sales, store transaction history, preserve item snapshots, and keep stock and audit data consistent.

This module is the most integrated operational module in the system. Build it after users/auth, audit, and inventory are stable enough, because transaction behavior depends on all of them.

## Module at a Glance
- Transaction header lives in `transactions`
- Line items live in `transaction_items`
- Current business logic already exists in `TransactionService`
- UI surface is still missing in this repo
- This module depends on valid user, valid product, available stock, and audit logging

## Schema Map
Primary tables:
- `transactions`
- `transaction_items`

Important `transactions` columns from the current MySQL table:
- `id`
- `transaction_code` - unique
- `user_id` - FK to `users.id`
- `total_amount`
- `paid_amount`
- `change_amount`
- `payment_method` - enum: `cash`, `qris`, `transfer`
- `status` - enum: `completed`, `cancelled`, `refunded`
- `transaction_date`
- `created_at`, `updated_at`

Important `transaction_items` columns:
- `id`
- `transaction_id` - FK to `transactions.id`
- `product_id` - FK to `products.id`
- `product_name` - snapshot
- `price` - snapshot
- `quantity`
- `subtotal`

Critical business meaning:
- Transaction items are snapshots, not only joins to the current product record
- A completed sale decreases stock
- Cancelled/refunded sale restores stock

Enhancement policy:
- If cashier flow needs a cart/session table, discount support, receipt number, or customer reference, the junior developer may add columns or new tables
- Keep first implementation lean unless the user explicitly asks for more POS features

## Current Repo Touchpoints
- Service: `app/Services/TransactionService.php`
- Models: `app/Models/Transaction.php`, `app/Models/TransactionItem.php`
- Related dependencies:
  - `app/Models/Product.php`
  - `app/Services/StockService.php`
  - `app/Services/AuditService.php`
- ERD reference: `erd-explanation.txt`

## UI Guidance Aligned with Product Management
The POS page may be more cashier-oriented than products management, but it must still feel like the same application.

Keep these shared patterns:
- Flux-based components
- clear page header and supporting actions
- products displayed in a structured, consistent layout
- order summary presented cleanly
- status badges for transaction state
- modal or panel flow for cancellation/refund confirmation
- empty states, feedback messages, and spacing consistent with Products Management

Recommended POS surfaces:
- Cashier page for creating sale
- Transaction history page with `flux:table`
- Detail modal/page for a finished transaction
- Confirmation modal for cancel/refund actions

Recommended history table columns:
- transaction code
- date/time
- cashier/user
- total amount
- payment method
- status
- actions

Allowed improvisation:
- A cart sidebar or split layout is allowed because POS interaction needs faster scanning than CRUD pages
- Payment summary area may be more prominent than in products/inventory

## Domain Rules, Validation, and Failure Cases
Business rules:
- Transaction must contain at least one item
- Product must still exist and be active when sold
- Stock must be sufficient for each item
- Paid amount must be greater than or equal to total amount
- `change_amount` must be computed, not typed manually as source of truth
- Completed transaction can move to cancelled or refunded only according to defined rules
- Cancellation/refund must restore stock and write audit log

Failure cases to handle:
- empty cart
- inactive product in cart
- insufficient stock
- paid amount lower than total
- repeated cancel/refund on already non-completed transaction
- stale product price after item added to cart but before transaction commit

## Implementation Slices
### Slice 1 - Build transaction history and read surface
- Add route and table for transaction listing
- Show status, payment method, cashier, amount, and date
- Add detail view/modal for transaction items

### Slice 2 - Build cashier POS flow
- Product selection surface
- Quantity control
- Running total calculation
- Payment method and paid amount input
- Submit through `TransactionService`

### Slice 3 - Add post-sale operations
- Cancel transaction
- Refund transaction
- Confirmation modal for destructive state change
- Readable audit/history feedback

### Slice 4 - Harden integration behavior
- Confirm stock movement history and audit logs are correct
- Confirm item snapshot fields are stored correctly even if product changes later

## TDD Plan
Follow RED -> GREEN -> REFACTOR.

Write tests before production changes in this order:
1. create transaction success path through `TransactionService`
2. reject empty items
3. reject inactive product
4. reject insufficient stock
5. reject paid amount below total
6. cancel transaction restores stock
7. refund transaction restores stock
8. transaction history UI and detail view
9. cashier POS UI happy path

Suggested test files:
- `tests/Unit/Services/TransactionServiceTest.php`
- `tests/Feature/TransactionManagementTest.php`
- `tests/Feature/PosFlowTest.php`

## Production-Grade Unit Testing Mechanism
### 1. Service-level tests
Must verify:
- transaction header and items are created correctly
- `transaction_code` exists and is unique enough for operational use
- `total_amount`, `paid_amount`, and `change_amount` are correct
- item snapshots use current product name/price at transaction time
- stock decreases on create
- stock restores on cancel/refund
- audit row is created for create/cancel/refund

### 2. Feature/Livewire tests
Must verify:
- cashier can access POS flow according to chosen authorization policy
- transaction history page renders rows correctly
- filters by status/payment/date work if implemented
- detail view shows items and amounts correctly
- cancel/refund confirmation flow behaves correctly

### 3. Transaction safety assertions
Must verify atomic behavior:
- if one item fails stock validation, transaction is not partially written
- if payment validation fails, nothing is persisted
- stock and transaction rows stay in sync after failure

### 4. Database assertions
Assert directly against:
- `transactions.transaction_code`
- `transactions.payment_method`
- `transactions.status`
- `transactions.total_amount`
- `transactions.paid_amount`
- `transactions.change_amount`
- `transaction_items.product_name`
- `transaction_items.price`
- `transaction_items.quantity`
- `stock_movements` side effects
- `activity_logs` side effects

Production-grade expectation:
- This module must have the strongest regression coverage among all modules
- Focus on money, stock consistency, and rollback safety
- Assert complete business side effects, not only the transaction row itself

## Acceptance Checklist
- The document is grounded in the current `transactions` and `transaction_items` tables
- The UI guidance respects products-management style while allowing a cashier-optimized layout
- Snapshot behavior and stock side effects are explicitly documented
- The document allows schema/table enhancement only when justified by POS needs
- The testing mechanism covers service logic, UI flow, DB integrity, and rollback behavior
