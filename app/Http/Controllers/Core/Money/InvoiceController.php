<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;

class InvoiceController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(__('Invoices'), __('Invoices scoped to the current company.'));
    }

    public function show(): View
    {
        return $this->placeholder(__('Invoice'), __('Invoice detail scoped to the current company.'));
    }
}
