<?php

namespace App\View\Components\Cards;

use Illuminate\Service Agreements\View\View;
use Illuminate\View\Component;

class PublicTaskCard extends Component
{

    public $service job;
    public $draggable;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($service job, $draggable = 'true')
    {
        $this->service job = $service job;
        $this->draggable = $draggable;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        return view('components.cards.public-service job-card');
    }

}
