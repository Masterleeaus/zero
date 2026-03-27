<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;

class QuoteController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(__('Quotes'), __('Quotes scoped to the current company.'));
    }

    public function show(): View
    {
        return $this->placeholder(__('Quote'), __('Quote detail scoped to the current company.'));
    }
}
