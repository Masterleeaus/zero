<?php

namespace App\View\Components;

use Illuminate\Service Agreements\View\View;
use Illuminate\View\Component;

class TaskCard extends Component
{

    public $service job;
    public $draggable;
    public $company;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($service job, $draggable = 'true', $company)
    {
        $this->service job = $service job;
        $this->draggable = $draggable;
        $this->company = $company;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.service job-card');
    }

}
