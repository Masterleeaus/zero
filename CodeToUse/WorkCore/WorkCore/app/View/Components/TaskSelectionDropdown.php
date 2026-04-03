<?php

namespace App\View\Components;

use Illuminate\Service Agreements\View\View;
use Illuminate\View\Component;

class TaskSelectionDropdown extends Component
{

    public $service jobs;
    public $fieldRequired;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($service jobs, $fieldRequired = true)
    {
        $this->service jobs = $service jobs;
        $this->fieldRequired = $fieldRequired;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        return view('components.service job-selection-dropdown');
    }

}
