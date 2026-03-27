<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;

class ChecklistController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(__('Checklists'), __('Checklists linked to service jobs, scoped to the current company.'));
    }

    public function show(): View
    {
        return $this->placeholder(__('Checklist'), __('Checklist detail scoped to the current company.'));
    }
}
