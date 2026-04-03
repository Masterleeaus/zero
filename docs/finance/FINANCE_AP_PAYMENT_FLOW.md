# FINANCE — AP PAYMENT FLOW

## Lifecycle

```
Draft Bill
   → (status=awaiting_payment) → Journal posted: Dr Expense / Cr AP
   → Record Payment             → Journal posted: Dr AP / Cr Bank
   → (amount_paid >= total)     → status = paid
   → (partial paid)             → status = partial
```

## SupplierBillService::recordPayment()

1. Creates `SupplierPayment` record
2. Updates `supplier_bills.amount_paid`
3. Sets status to `paid` or `partial`
4. Calls `AccountingService::postSupplierPaymentRecorded()`
5. Dispatches `SupplierPaymentRecorded` event

## SupplierPaymentObserver

Registered on `SupplierPayment::created`.
Calls `AccountingService::postSupplierPaymentRecorded()`.
The service is idempotent — if the service already posted it, the observer is a no-op.

## Journal Entry

```
Dr  Accounts Payable  (2000)  amount
Cr  Bank              (1000)  amount
```

## Future

- Payment account can be any `accounts` row (bank, cash, credit card)
- Multi-currency support: journal currency follows `SupplierPayment.currency` when added
- Bulk payment allocation across multiple bills
