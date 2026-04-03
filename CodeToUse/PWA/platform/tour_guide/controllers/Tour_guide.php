<?php

defined('BASEPATH') or exit('No direct script access allowed');


use TourGuide\Http\Controllers\TourGuideAdminControllerTrait;
use TourGuide\Http\TourGuideServiceTrait;

require __DIR__ . '/TourGuideLocalTrait.php';

/**
 * Tour_guide admin Controller
 *
 * Handles CRUD operations for the tour guide feature within the admin panel.
 * Provides functionality for viewing, creating, updating, and deleting tour guides.
 */
class Tour_guide extends AdminController
{

    use TourGuideServiceTrait;
    use TourGuideAdminControllerTrait;
    use TourGuideLocalTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initTransformer();
    }
}