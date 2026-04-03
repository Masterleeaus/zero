# FINANCE — AP POLICY MAP

All policies are registered in `App\Providers\AuthServiceProvider`.

## SupplierPolicy

Model: `App\Models\Inventory\Supplier`

| Method  | Rule                              |
|---------|-----------------------------------|
| viewAny | company_id not null               |
| view    | supplier.company_id == user.company_id |
| create  | company_id not null               |
| update  | supplier.company_id == user.company_id |
| delete  | supplier.company_id == user.company_id |

## PurchaseOrderPolicy

Model: `App\Models\Inventory\PurchaseOrder`

| Method  | Rule                              |
|---------|-----------------------------------|
| viewAny | company_id not null               |
| view    | po.company_id == user.company_id  |
| create  | company_id not null               |
| update  | po.company_id == user.company_id  |
| delete  | po.company_id == user.company_id  |

## SupplierBillPolicy

Model: `App\Models\Money\SupplierBill`

| Method        | Rule                                              |
|---------------|---------------------------------------------------|
| viewAny       | company_id not null                               |
| view          | bill.company_id == user.company_id                |
| create        | company_id not null                               |
| update        | bill.company_id == user.company_id && !void       |
| delete        | bill.company_id == user.company_id && draft only  |
| recordPayment | bill.company_id == user.company_id && !paid/void  |

## SupplierPaymentPolicy

Model: `App\Models\Money\SupplierPayment`

| Method  | Rule                                                  |
|---------|-------------------------------------------------------|
| viewAny | company_id not null                                   |
| view    | payment.company_id == user.company_id                 |
| create  | bill.company_id == user.company_id && !paid/void      |
