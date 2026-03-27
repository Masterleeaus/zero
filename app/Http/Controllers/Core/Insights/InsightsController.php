<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Insights;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;

class InsightsController extends CoreController
{
    public function overview(): View
    {
        return $this->placeholder(__('Insights Overview'), __('High-level metrics for the current company.'));
    }

    public function reports(): View
    {
        return $this->placeholder(__('Reports'), __('Reports scoped to the current company.'));
    }
}
