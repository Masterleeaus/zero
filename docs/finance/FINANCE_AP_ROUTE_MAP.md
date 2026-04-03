# FINANCE — AP ROUTE MAP

All routes live under prefix `dashboard/money` with name prefix `dashboard.money.`.

## Suppliers

| Method | URI                              | Name                         |
|--------|----------------------------------|------------------------------|
| GET    | money/suppliers                  | money.suppliers.index        |
| GET    | money/suppliers/create           | money.suppliers.create       |
| POST   | money/suppliers                  | money.suppliers.store        |
| GET    | money/suppliers/{supplier}       | money.suppliers.show         |
| GET    | money/suppliers/{supplier}/edit  | money.suppliers.edit         |
| PUT    | money/suppliers/{supplier}       | money.suppliers.update       |
| DELETE | money/suppliers/{supplier}       | money.suppliers.destroy      |

## Purchase Orders

| Method | URI                                          | Name                              |
|--------|----------------------------------------------|-----------------------------------|
| GET    | money/purchase-orders                        | money.purchase-orders.index       |
| GET    | money/purchase-orders/create                 | money.purchase-orders.create      |
| POST   | money/purchase-orders                        | money.purchase-orders.store       |
| GET    | money/purchase-orders/{purchaseOrder}        | money.purchase-orders.show        |
| GET    | money/purchase-orders/{purchaseOrder}/edit   | money.purchase-orders.edit        |
| PUT    | money/purchase-orders/{purchaseOrder}        | money.purchase-orders.update      |
| DELETE | money/purchase-orders/{purchaseOrder}        | money.purchase-orders.destroy     |

## Supplier Bills

| Method | URI                                             | Name                              |
|--------|-------------------------------------------------|-----------------------------------|
| GET    | money/supplier-bills                            | money.supplier-bills.index        |
| GET    | money/supplier-bills/create                     | money.supplier-bills.create       |
| POST   | money/supplier-bills                            | money.supplier-bills.store        |
| GET    | money/supplier-bills/{supplierBill}             | money.supplier-bills.show         |
| GET    | money/supplier-bills/{supplierBill}/edit        | money.supplier-bills.edit         |
| PUT    | money/supplier-bills/{supplierBill}             | money.supplier-bills.update       |
| POST   | money/supplier-bills/{supplierBill}/payments    | money.supplier-payments.store     |

## Controller namespace

`App\Http\Controllers\Core\Money\`
