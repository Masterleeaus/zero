<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;

class CustomerController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(__('Customers'), __('Manage customers within the current company.'));
    }

    public function show(): View
    {
        return $this->placeholder(__('Customer'), __('Customer detail within the current company.'));
    }
}
