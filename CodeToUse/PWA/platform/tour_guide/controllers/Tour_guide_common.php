<?php

defined('BASEPATH') or exit('No direct script access allowed');

use TourGuide\Http\Controllers\TourGuideCommonControllerTrait;
use TourGuide\Http\TourGuideServiceTrait;
use TourGuide\TourGuideHook;

require __DIR__ . '/TourGuideLocalTrait.php';

/**
 * Tour_guide_user Controller
 *
 * Handles tour users reporting like last view, finished toure e.t.c
 */
class Tour_guide_common extends App_Controller
{
    use TourGuideServiceTrait;
    use TourGuideCommonControllerTrait;
    use TourGuideLocalTrait;

    public function __construct()
    {
        parent::__construct(false);

        $this->initTransformer();

        TourGuideHook::addFilter("before_handle_setup", function ($action, $portal) {
            // When starting setup in staff, logout the current admin and creat a session to store the tour id
            if ($action == 'start' && $portal == 'staff') {

                $this->load->model("authentication_model");
                $this->authentication_model->logout(true);
                session_start();
                $this->session->sess_regenerate(true);
                //hooks()->do_action('after_client_logout');
            }
        });

        TourGuideHook::addFilter("after_handle_setup_return_url", function ($data) {
            $action = $data['action'] ?? '';
            $portal = $data['portal'] ?? '';

            $url = $this->staffCan('edit') ?  tourGuideHelper()->adminUrl() : admin_url();
            if ($action == 'start') {
                $url = admin_url();
                if ($portal == 'client')
                    $url = base_url('clients');
            }

            return $url;
        });
    }
}