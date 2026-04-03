# FINANCE — AP POSTING RULES

## Supplier Bill Posted

```
Dr  Operating Expenses (6000)   amount = bill.total
Cr  Accounts Payable   (2000)   amount = bill.total
```

Triggered when:
- Bill status transitions to `awaiting_payment`
- Bill is created with status != `draft`

Service: `AccountingService::postSupplierBillRecorded(SupplierBill $bill)`

## Supplier Payment Made

```
Dr  Accounts Payable (2000)   amount = payment.amount
Cr  Bank / Cash      (1000)   amount = payment.amount
```

Triggered on: `SupplierPayment::created` via `SupplierPaymentObserver`

Service: `AccountingService::postSupplierPaymentRecorded(SupplierPayment $payment)`

## Idempotency

Both methods check `alreadyPosted(sourceType, sourceId)` before creating a new entry.
Duplicate calls are silently skipped.

## Account Auto-Creation

If the required account codes do not exist for the company, they are auto-created:

| Code | Name                 | Type      |
|------|----------------------|-----------|
| 1000 | Bank                 | asset     |
| 2000 | Accounts Payable     | liability |
| 6000 | Operating Expenses   | expense   |
