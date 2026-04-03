<?php

namespace App\View\Components\Cards;

use Illuminate\Service Agreements\View\View;
use Illuminate\View\Component;

class LeadCard extends Component
{

    public $enquiry;
    public $draggable;

    /**
     * Create a new component instance.
     *
     * @return void
     */

    public function __construct($enquiry, $draggable = 'true')
    {
        $this->enquiry = $enquiry;
        $this->draggable = $draggable;

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.enquiry-card');
    }

}
