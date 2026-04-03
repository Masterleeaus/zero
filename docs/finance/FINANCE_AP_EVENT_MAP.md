# FINANCE — AP EVENT MAP

## Events (App\Events\Money\)

| Event                  | Payload          | Triggered by                               |
|------------------------|------------------|--------------------------------------------|
| SupplierCreated        | Supplier         | SupplierController::store()                |
| PurchaseOrderIssued    | PurchaseOrder    | PurchaseOrderController when status=sent   |
| SupplierBillRecorded   | SupplierBill     | SupplierBillService::createBill()          |
| SupplierPaymentRecorded| SupplierPayment  | SupplierBillService::recordPayment()       |

## Observers (App\Observers\Money\)

| Observer                  | Model           | Event    | Action                              |
|---------------------------|-----------------|----------|-------------------------------------|
| SupplierPaymentObserver   | SupplierPayment | created  | postSupplierPaymentRecorded()       |

## Registration

Observers should be registered in a ServiceProvider boot():

```php
SupplierPayment::observe(SupplierPaymentObserver::class);
```

## Future Listeners

- `SupplierBillRecorded` → update dashboard AP aging report
- `SupplierCreated` → sync to CRM contacts if CRM module present
- `PurchaseOrderIssued` → notify purchasing team
