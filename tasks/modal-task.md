# Work Instruction: Refactor Product Management to Modal-based Flow

## Objective
Refactor the current Product Create and Edit flows from separate pages to a single modal dialog. Improve the Delete action with a custom confirmation modal.

## Technical Context
- **Framework**: Laravel 11 + Livewire 3
- **UI Kit**: Flux UI
- **Existing Components**:
    - `App\Livewire\Products\ProductList`: Main catalog view.
    - `App\Livewire\Products\ProductForm`: Handles product creation and editing.

---

## Task 1: Refactor `ProductForm` for Modal Usage

### 1.1 Modify `ProductForm.php`
- Location: `app/Livewire/Products/ProductForm.php`
- Remove the `#[Layout('components.layouts.app')]` attribute as this component will no longer be a standalone page.
- Update the `save` method:
    - Instead of `return redirect()->route('products.index');`, dispatch a browser event or a Livewire event to notify the parent that the operation is complete.
    - Example: `$this->dispatch('product-saved');` or just let the parent handle the modal closing.
    - Keep the `session()->flash('success', $message);` or use `$this->toast()` if preferred for consistency.

### 1.2 Update `product-form.blade.php`
- Location: `resources/views/livewire/products/product-form.blade.php`
- Ensure the form layout fits well inside a modal (remove extra containers if they were page-specific).
- Add a "Cancel" button that closes the modal using `$flux.modal('product-form-modal').close()`.

---

## Task 2: Integrate Modal in `ProductList`

### 2.1 Update `ProductList.php`
- Location: `app/Livewire/Products/ProductList.php`
- Add a property `public $editingProductId = null;` to track which product is being edited (null for create).
- Update `openAddModal()`:
    - Set `$this->editingProductId = null;`
    - Trigger the modal: `$this->js('$flux.modal("product-form-modal").show()');`
    - Remove the `redirect()` call.
- Update `editProduct($productId)`:
    - Set `$this->editingProductId = $productId;`
    - Trigger the modal: `$this->js('$flux.modal("product-form-modal").show()');`
    - Remove the `redirect()` call.
- Add a listener for the `product-saved` event to close the modal and refresh the list:
    ```php
    #[On('product-saved')]
    public function handleProductSaved()
    {
        $this->js('$flux.modal("product-form-modal").close()');
        // The list will automatically refresh if the component re-renders
    }
    ```

### 2.2 Update `product-list.blade.php`
- Location: `resources/views/livewire/products/product-list.blade.php`
- Add the Flux Modal at the bottom of the file:
    ```html
    <flux:modal name="product-form-modal" class="min-w-[30rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingProductId ? 'Edit Product' : 'Add Product' }}</flux:heading>
                <flux:subheading>Fill in the details below.</flux:subheading>
            </div>

            <livewire:products.product-form 
                :product-id="$editingProductId" 
                :key="'product-form-'.$editingProductId" 
            />
        </div>
    </flux:modal>
    ```
- **Note**: Using `:key` ensures the `ProductForm` component is re-initialized whenever `$editingProductId` changes.

---

## Task 3: Improve Delete Confirmation

### 3.1 Use Flux Confirmation Modal
- Replace the current `wire:confirm` in `product-list.blade.php` with a dedicated Flux confirmation modal.
- Recommended approach:
    1. In `ProductList.php`, add `public $productToDelete = null;`.
    2. Add `confirmDelete($productId)` method to set the ID and open `delete-product-modal`.
    3. Add the modal to `product-list.blade.php`:
    ```html
    <flux:modal name="delete-product-modal" class="min-w-[25rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Product?</flux:heading>
                <flux:subheading>
                    <p>Are you sure you want to delete this product? <strong>This action cannot be undone.</strong></p>
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button color="danger" wire:click="deleteProduct">Delete Product</flux:button>
            </div>
        </div>
    </flux:modal>
    ```
    4. Update `deleteProduct()` to use the `$productToDelete` property and close the modal after deletion.

---

## Task 4: Cleanup Routes
- Location: `routes/web.php`
- Remove the routes for `products.create` and `products.edit` as they are now handled by the modal on the `products.index` page.

---

## UX Expectations
- **Instant Interaction**: No full-page reloads when clicking "Add" or "Edit".
- **State Management**: Form should be empty when "Add" is clicked after "Edit" (handled by `:key`).
- **Feedback**: Use Toast notifications for success/error messages.
- **Consistency**: The modal design should match the existing "Price History" modal.

## Technical Constraints
- Do not add new JS libraries.
- Use Flux UI components (`flux:modal`, `flux:button`, etc.) exclusively.
- Ensure validation errors are still displayed correctly within the modal.
