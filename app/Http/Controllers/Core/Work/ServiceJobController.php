<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;

class ServiceJobController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(__('Service Jobs'), __('Bookable work items for a site, scoped to the current company.'));
    }

    public function show(): View
    {
        return $this->placeholder(__('Service Job'), __('Service job detail scoped to the current company.'));
    }
}
