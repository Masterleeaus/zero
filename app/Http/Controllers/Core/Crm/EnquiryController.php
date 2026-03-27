<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;

class EnquiryController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(__('Enquiries'), __('Track new enquiries for the current company.'));
    }

    public function show(): View
    {
        return $this->placeholder(__('Enquiry'), __('Enquiry detail within the current company.'));
    }
}
