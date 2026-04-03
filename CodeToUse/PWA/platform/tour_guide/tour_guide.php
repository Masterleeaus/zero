<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Tour Guide
Description: Create and run interactive onboarding tours for both staff and customers with zero code using click/drag-drop.
Version: 1.0.1
Requires at least: 3.1.*
Author: ulutfa
Author URI: https://codecanyon.net/user/ulutfa
*/

defined('TOUR_GUIDE_MODULE_NAME') or define('TOUR_GUIDE_MODULE_NAME', 'tour_guide');

$CI = &get_instance();

/**
 * Load the helpers
 */
$CI->load->helper(TOUR_GUIDE_MODULE_NAME . '/' . TOUR_GUIDE_MODULE_NAME);

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(TOUR_GUIDE_MODULE_NAME, [TOUR_GUIDE_MODULE_NAME, TOUR_GUIDE_MODULE_NAME . '_core']);

/**
 * Register activation module hook
 */
register_activation_hook(TOUR_GUIDE_MODULE_NAME, function () {
    require_once(__DIR__ . '/install.php');
});



hooks()->add_action('admin_init', 'tour_guide_module_init_menu_items');
hooks()->add_action('app_admin_footer', 'tour_guide_add_head_components');
hooks()->add_action('app_admin_authentication_head', 'tour_guide_add_head_components');

hooks()->add_action('app_customers_head', 'tour_guide_add_head_components');

function tour_guide_module_init_menu_items()
{
    $CI = &get_instance();
    if (has_permission('tour_guide', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('tour-guide', [
            'name'     => tourGuideHelper()->translate('tour_guide'),
            'icon'     => 'fa fa-location-arrow',
            'href'     => tourGuideHelper()->adminUrl(),
            'position' => 45,
        ]);
    }

    // Register permssion
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities(TOUR_GUIDE_MODULE_NAME, $capabilities, tourGuideHelper()->translate(TOUR_GUIDE_MODULE_NAME));
}

function tour_guide_add_head_components()
{
    get_instance()->load->view(TOUR_GUIDE_MODULE_NAME . '/tour-guide-init');
}