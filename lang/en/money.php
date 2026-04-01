<?php

return [
    'quotes'             => 'Quotes',
    'quote'              => 'Quote',
    'invoices'           => 'Invoices',
    'invoice'            => 'Invoice',
    'payments'           => 'Payments',
    'payment'            => 'Payment',
    'expenses'           => 'Expenses',
    'expense'            => 'Expense',
    'credit_notes'       => 'Credit Notes',
    'credit_note'        => 'Credit Note',
    'bank_accounts'      => 'Bank Accounts',
    'bank_account'       => 'Bank Account',
    'taxes'              => 'Taxes',
    'tax'                => 'Tax',
    'outstanding_balance' => 'Outstanding Balance',
    'overdue'            => 'Overdue',
    'mark_paid'          => 'Mark Paid',
    'convert_to_invoice' => 'Convert to Invoice',
    'quotes' => [
        'title'         => 'Quotes',
        'new'           => 'New Quote',
        'empty'         => 'No quotes yet',
        'convert_job'   => 'Convert to ' . workcore_label('service_job'),
        'create_job'    => 'Create ' . workcore_label('service_job'),
        'site'          => workcore_label('site'),
    ],

    'invoices' => [
        'title' => 'Invoices',
        'new'   => 'New Invoice',
        'empty' => 'No invoices yet',
    ],

    'expenses' => [
        'title' => 'Expenses',
        'new'   => 'New Expense',
        'empty' => 'No expenses yet',
    ],

    'payments' => [
        'title' => 'Payments',
        'empty' => 'No payments yet',
    ],
];
