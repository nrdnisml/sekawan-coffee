# Work Instruction: Inventory & Stock Module

## Objective
Build and refine the inventory module so the team can see current stock, record manual stock movement, and review stock history with clear auditability.

This module is already partially implemented in the repo, so the junior developer should treat this instruction as a normalization and production-hardening guide, not as a greenfield feature.

## Module at a Glance
- Products hold the current stock number in `products.stock`
- Stock movement history lives in `stock_movements`
- The current repo already includes inventory Livewire pages and stock services
- Transactions depend on this module because sales reduce stock and cancellation/refund restore stock

## Schema Map
Operational tables used by this module:
- `stock_movements` - primary history table for inventory behavior
- `products` - dependency table used only as the current stock source and product reference for inventory operations

Important `products` columns used by this module as dependencies:
- `id`
- `name`
- `stock`
- `is_active`

Scope guard:
- This instruction is for inventory operations only.
- Product CRUD, product pricing, and product catalog management remain part of the products module and are out of scope here.

Important `stock_movements` columns from the current MySQL table:
- `id`
- `product_id`
- `type` - enum: `in`, `out`, `adjustment`
- `quantity`
- `reference_type`
- `reference_id`
- `note`
- `created_at`

Inventory invariants:
- `products.stock` is the latest operational stock value
- `stock_movements` is the history trail
- `out` must not reduce stock below zero
- `adjustment` represents a stock set/reconciliation event in current service behavior

Enhancement policy:
- If stock control needs reason codes, batch numbers, supplier references, or approval flow, the junior developer may enhance `stock_movements` or add related tables
- Any enhancement must preserve backward readability of existing movements

## Current Repo Touchpoints
- Page: `app/Livewire/Inventory/InventoryList.php`
- Form: `app/Livewire/Inventory/StockAdjustmentForm.php`
- History viewer: `app/Livewire/Inventory/StockMovementHistory.php`
- Views: `resources/views/livewire/inventory/*.blade.php`
- Service: `app/Services/StockService.php`
- Model: `app/Models/StockMovement.php`
- Route: `routes/web.php`
- Existing test baseline: `tests/Feature/InventoryManagementTest.php`

## UI Guidance Aligned with Product Management
This module should visually feel like the sibling of Products Management.

Required UX direction:
- Header at top with clear title
- Search/filter area above the table
- `flux:table` for stock overview
- Modal-based form for stock adjustment
- Modal-based history viewer for movement trail
- Status badge reuses product active/inactive visual pattern
- No full page reload after adjustments
- Pagination and empty states match the products screen tone

Suggested table columns:
- product name
- current stock
- active status
- last movement time if useful
- actions: adjust stock, view history

Allowed improvisation:
- Add low-stock badge or warning styling if it improves operator clarity
- Add separate filter for active/inactive or low-stock state if useful

## Domain Rules, Validation, and Failure Cases
Business rules:
- `in` increases stock
- `out` decreases stock
- `adjustment` should follow current service meaning: set/reconcile stock value
- Stock cannot go negative through `out`
- Inactive products should still be viewable in history, even if they are not currently sellable

Failure cases to handle:
- operator tries to remove more stock than available
- invalid movement type
- zero or negative values submitted in the wrong mode
- product no longer exists when modal opens

## Implementation Slices
### Slice 1 - Stabilize current page behavior
- Confirm route, search, table, and pagination are reliable
- Confirm modal open/close flow is clean
- Confirm toast or success feedback appears after update

### Slice 2 - Harden stock adjustment rules
- Keep validation close to UI
- Keep real stock protection in `StockService`
- Convert service exceptions into user-friendly form errors

### Slice 3 - Improve history usefulness
- Show clear type badges
- Show source/reference meaning clearly
- Keep note visible and readable

### Slice 4 - Prepare for POS integration
- Confirm transaction-created movements appear in history
- Confirm movement rows remain understandable when driven by other modules

## TDD Plan
Follow RED -> GREEN -> REFACTOR.

Write tests before production changes in this order:
1. service-level stock increase
2. service-level stock decrease with insufficient stock guard
3. service-level adjustment behavior
4. Livewire adjustment form success path
5. Livewire adjustment form validation/error path
6. stock history view
7. transaction-driven stock movement regression

Suggested test files:
- extend `tests/Feature/InventoryManagementTest.php`
- add `tests/Unit/Services/StockServiceTest.php` if service logic grows

## Production-Grade Unit Testing Mechanism
### 1. Service-level tests
Must verify:
- `increaseStock()` increments stock and writes `stock_movements`
- `decreaseStock()` decrements stock and rejects insufficient stock
- `adjustStock()` follows intended reconciliation behavior and writes movement row

### 2. Livewire component tests
Must verify:
- inventory page renders products
- search/filter narrows results
- adjustment form dispatches success event
- invalid `out` action surfaces an error on the form
- history component shows correct rows and signs (`+` / value)

### 3. Cross-module regression tests
Must verify:
- creating a transaction reduces stock
- cancelling or refunding a transaction restores stock
- corresponding movement history becomes visible in the inventory module

### 4. Database assertions
Assert directly against:
- `products.stock`
- `stock_movements.type`
- `stock_movements.quantity`
- `stock_movements.reference_type`
- `stock_movements.reference_id`
- `stock_movements.note`

Production-grade expectation:
- Do not stop at UI assertions only
- Always assert stock number and movement row together
- Cover both manual flow and transaction side effects

## Acceptance Checklist
- The document reflects the real current repo structure for inventory
- The UI guidance clearly follows product management style
- The rules for `in`, `out`, and `adjustment` are explicit
- The document points the junior to both current stock and stock history tables
- The testing mechanism covers service logic, Livewire flow, and transaction regression
