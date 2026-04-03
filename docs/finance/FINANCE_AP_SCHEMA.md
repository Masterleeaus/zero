# FINANCE — AP SCHEMA

## supplier_bills

| Column             | Type          | Notes                                          |
|--------------------|---------------|------------------------------------------------|
| id                 | bigint PK     |                                                |
| company_id         | bigint        | tenancy scope                                  |
| created_by         | bigint        | nullable                                       |
| supplier_id        | bigint FK     | → suppliers.id                                 |
| purchase_order_id  | bigint FK     | nullable → purchase_orders.id                  |
| reference          | string        | nullable                                       |
| bill_date          | date          |                                                |
| due_date           | date          | nullable                                       |
| currency           | string(10)    | default AUD                                    |
| subtotal           | decimal(14,2) |                                                |
| tax_total          | decimal(14,2) |                                                |
| total              | decimal(14,2) |                                                |
| amount_paid        | decimal(14,2) |                                                |
| status             | string        | draft/awaiting_payment/partial/paid/overdue/void |
| notes              | text          | nullable                                       |

## supplier_bill_lines

| Column           | Type          | Notes                          |
|------------------|---------------|--------------------------------|
| id               | bigint PK     |                                |
| supplier_bill_id | bigint FK     | → supplier_bills.id            |
| account_id       | bigint        | nullable                       |
| service_job_id   | bigint        | nullable — job costing hook    |
| description      | string        | nullable                       |
| amount           | decimal(14,2) |                                |
| tax_rate         | decimal(8,4)  |                                |
| tax_amount       | decimal(14,2) |                                |

## supplier_payments

| Column             | Type          | Notes                          |
|--------------------|---------------|--------------------------------|
| id                 | bigint PK     |                                |
| company_id         | bigint        | tenancy scope                  |
| created_by         | bigint        | nullable                       |
| supplier_bill_id   | bigint FK     | → supplier_bills.id            |
| payment_account_id | bigint        | nullable → accounts.id         |
| amount             | decimal(14,2) |                                |
| payment_date       | date          |                                |
| reference          | string        | nullable                       |
| notes              | text          | nullable                       |

## Extended: suppliers

| Column             | Type    | Notes                          |
|--------------------|---------|--------------------------------|
| default_account_id | bigint  | nullable → accounts.id (AP)    |

## Extended: purchase_orders

| Column         | Type   | Notes                                  |
|----------------|--------|----------------------------------------|
| service_job_id | bigint | nullable — job costing bridge          |
