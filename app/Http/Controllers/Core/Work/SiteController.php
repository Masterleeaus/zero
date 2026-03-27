<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;

class SiteController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(__('Sites'), __('Sites (projects) scoped to the current company.'));
    }

    public function show(): View
    {
        return $this->placeholder(__('Site'), __('Site detail scoped to the current company.'));
    }
}
