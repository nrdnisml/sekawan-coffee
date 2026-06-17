# Project Feature List: Sekawan Coffee POS

This document outlines the business features currently available in the project (based on the Service Layer analysis) and identifies features that are either missing or could be improved to build a complete POS system.

## 1. Available Business Features

### **Product Management (`ProductService`)**
- **CRUD Operations**: Create, Read, Update, and Delete (with soft-delete/validation against history).
- **Price History**: Automatic tracking of product price changes.
- **Status Control**: Ability to activate/deactivate products.
- **Modal-based UI**: (Recently implemented) Inline management for a faster workflow.

### **Inventory & Stock Management (`StockService`)**
- **Stock Movements**: Tracking "IN", "OUT", and "ADJUSTMENT" types.
- **Auto-deduction**: Stock is automatically reduced when a transaction is completed.
- **Stock Restoration**: Stock is automatically restored when a transaction is cancelled or refunded.
- **Stock Validation**: Prevents sales of products with insufficient stock.

### **Transaction & POS (`TransactionService`)**
- **Checkout Process**: Create transactions with multiple items, payment method selection, and change calculation.
- **Transaction Status**: Tracking of `completed`, `cancelled`, and `refunded` states.
- **Code Generation**: Unique transaction code generation (e.g., `TXN-XXXXXXXXXX`).

### **Expense Management (`ExpenseService`)**
- **Recording**: Track operational expenses with description and amount.
- **Filtering**: View expenses by date range or specific user.

### **User & Auth Management (`UserService`, `AuthService`)**
- **Role-based Access**: Basic differentiation between `admin` and `cashier`.
- **User CRUD**: Admin-only management of system users.
- **Authentication**: Secure login/logout flow with status checking.

### **System Auditing (`AuditService`)**
- **Activity Logs**: System-wide logging of critical actions (login, create, delete, etc.) across all entities.

---

## 2. Recommended / Missing Features

### **Sales & Financial Reporting**
- [ ] **Daily/Monthly Reports**: Aggregated view of total sales, expenses, and net profit.
- [ ] **Top-Selling Products**: Insights into which products are performing best.
- [ ] **Payment Method Breakdown**: Statistics on Cash vs. Digital payments.

### **Customer Management**
- [ ] **Customer Profiles**: Record customer names/phone numbers for loyalty tracking.
- [ ] **Loyalty/Points**: Basic reward system for frequent customers.

### **Enhanced POS UI/UX**
- [ ] **Real-time Order Summary**: Interactive cart sidebar in the POS view.
- [ ] **Receipt Generation**: Printing functionality or PDF export for customer receipts.
- [ ] **Quick Search/Categories**: Better organization for projects with many items (e.g., "Coffee", "Snacks").

### **Advanced Inventory**
- [ ] **Low Stock Alerts**: Notifications when product stock falls below a certain threshold.
- [ ] **Supplier Management**: Track where products are sourced from.

### **Operational Tools**
- [ ] **Shift Management**: Track "Clock In" and "Clock Out" for cashiers with cash-drawer balancing.
- [ ] **Discount & Promo Engine**: Support for percentage or flat-amount discounts on items or total transactions.

---

## 3. Technical Improvements (Roadmap)
- [ ] **Unit Testing Coverage**: Expand tests for `StockService` and `TransactionService`.
- [ ] **Export Functionality**: Export reports (Sales, Expenses) to Excel or CSV.
- [ ] **Dashboard Analytics**: Visual charts (using ApexCharts or similar) for the main dashboard.
