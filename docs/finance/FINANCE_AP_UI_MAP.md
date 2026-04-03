# FINANCE — AP UI MAP

## Views

All views extend `panel.layout.app`.

### Suppliers

| View                                                     | Route                      |
|----------------------------------------------------------|----------------------------|
| resources/views/default/panel/user/money/suppliers/index.blade.php | money.suppliers.index |
| resources/views/default/panel/user/money/suppliers/form.blade.php  | money.suppliers.create / edit |
| resources/views/default/panel/user/money/suppliers/show.blade.php  | money.suppliers.show  |

### Purchase Orders

| View                                                           | Route                           |
|----------------------------------------------------------------|---------------------------------|
| resources/views/default/panel/user/money/purchase-orders/index.blade.php | money.purchase-orders.index |
| resources/views/default/panel/user/money/purchase-orders/form.blade.php  | money.purchase-orders.create / edit |
| resources/views/default/panel/user/money/purchase-orders/show.blade.php  | money.purchase-orders.show  |

### Supplier Bills

| View                                                            | Route                            |
|-----------------------------------------------------------------|----------------------------------|
| resources/views/default/panel/user/money/supplier-bills/index.blade.php | money.supplier-bills.index |
| resources/views/default/panel/user/money/supplier-bills/form.blade.php  | money.supplier-bills.create / edit |
| resources/views/default/panel/user/money/supplier-bills/show.blade.php  | money.supplier-bills.show  |

The `show` view for Supplier Bills includes an inline **Record Payment** form.

## Design

- Extends `panel.layout.app` (host theme, no custom CSS)
- Uses x-card, x-table, x-button, x-input, x-select, x-badge components
- Filters via GET parameters (q, status)
- Minimal CRUD — no JavaScript required
