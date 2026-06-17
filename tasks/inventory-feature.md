# Work Instruction: Develop Inventory Frontend Features

## Objective
Develop the frontend interface for the **Inventory & Stock Management** feature. This will allow users to view current stock levels, perform manual stock adjustments (IN, OUT, ADJUSTMENT), and view the history of stock movements.

You will follow the **modal-based pattern** recently established in the Product Management feature to ensure a seamless, no-reload user experience.

## Technical Context
- **Backend Readiness**: The `App\Services\StockService` and `App\Services\TransactionService` already handle the core logic (Auto-deduction, Stock Restoration, Stock Validation).
- **Framework**: Laravel 11, Livewire 3
- **UI Kit**: Flux UI (`flux:table`, `flux:modal`, `flux:select`, etc.)

---

## Task 1: Create the Inventory List Component

**Goal:** A dedicated page to view all products and their current stock levels.

1. **Generate the Component**:
   Run: `php artisan make:livewire Inventory/InventoryList`
2. **Setup `InventoryList.php`**:
   - Add pagination (`use WithPagination;`).
   - Add a search filter (`public $search = '';`).
   - Add properties for modal state:
     ```php
     public $selectedProductId = null;
     public $adjustingProductId = null;
     ```
   - Add methods to trigger modals:
     ```php
     public function openAdjustmentModal($productId) { ... }
     public function openHistoryModal($productId) { ... }
     ```
   - Add a listener `#[On('stock-updated')]` to close the adjustment modal and show a success toast.
   - In `render()`, query `Product::query()` with the search filter and paginate.
3. **Setup `inventory-list.blade.php`**:
   - Use `flux:table` to display: Product Name, Current Stock, Status, and Actions.
   - For Actions, add two buttons:
     - "Adjust Stock" (triggers `openAdjustmentModal`)
     - "View History" (triggers `openHistoryModal`)
   - Add the two `flux:modal` containers at the bottom of the view (similar to `product-list.blade.php`).

---

## Task 2: Create the Stock Adjustment Modal

**Goal:** A form inside a modal to handle manual stock changes.

1. **Generate the Component**:
   Run: `php artisan make:livewire Inventory/StockAdjustmentForm`
2. **Setup `StockAdjustmentForm.php`**:
   - Properties: `public $productId;`, `public $type = 'in';`, `public $quantity = 1;`, `public $note = '';`
   - Validation rules:
     - `type`: required, in:in,out,adjustment
     - `quantity`: required, integer, min:1
     - `note`: nullable, string
   - The `save(StockService $stockService)` method:
     - Based on `$this->type`, call the appropriate service method:
       - `in`: `$stockService->increaseStock(..., 'manual', null, $this->note)`
       - `out`: `$stockService->decreaseStock(..., 'manual', null, $this->note)`
       - `adjustment`: `$stockService->adjustStock(..., $this->quantity, $this->note)` (Note: for adjustments, you may need to allow negative numbers, check `StockService` logic).
     - Catch any Exceptions (e.g., "Insufficient stock") and add a validation error to the component.
     - On success: `$this->dispatch('stock-updated');`
3. **Setup `stock-adjustment-form.blade.php`**:
   - Use `flux:select` for the `type` (In, Out, Adjustment).
   - Use `flux:input` for `quantity`.
   - Use `flux:textarea` for `note`.
   - Include a submit button and a cancel button that closes the modal (`$flux.modal('...')->close()`).

---

## Task 3: Create the Stock Movement History Modal

**Goal:** A view-only modal showing the audit trail of stock changes for a specific product.

1. **Generate the Component**:
   Run: `php artisan make:livewire Inventory/StockMovementHistory`
2. **Setup `StockMovementHistory.php`**:
   - Property: `public $productId;`
   - In `render()`, query `App\Models\StockMovement::where('product_id', $this->productId)->latest()->get();`
3. **Setup `stock-movement-history.blade.php`**:
   - Display a `flux:table` with columns: Date, Type (In/Out/Adjustment), Quantity, Source (Reference Type), and Note.
   - Use `flux:badge` to color-code the types (e.g., Green for IN, Red for OUT, Blue for ADJUSTMENT).

---

## Task 4: Hook Up Routing

1. **Update `routes/web.php`**:
   - Add the new route within the authenticated group:
     ```php
     Route::get('inventory', \App\Livewire\Inventory\InventoryList::class)->name('inventory.index');
     ```
2. **Update Navigation**:
   - Locate the main navigation menu (likely in `resources/views/components/layouts/app.blade.php` or a sidebar partial).
   - Add a link to the `inventory.index` route, placing it near the "Products" link.

---

## UX Expectations & Constraints
- **Validation**: If a user tries to do an "OUT" adjustment that exceeds current stock, the `StockService` will throw an Exception. You **must** catch this in Livewire and display it as an error message on the quantity field.
- **No Page Reloads**: Ensure the `:key` attribute is used when rendering the modal components inside `inventory-list.blade.php` so the forms reset cleanly when clicking between different products.
- **Auto-deduction & Restoration**: You do not need to build UI for this. Just verify through testing that when you create a transaction (via existing code), the `StockMovementHistory` modal automatically displays a "transaction" movement type.