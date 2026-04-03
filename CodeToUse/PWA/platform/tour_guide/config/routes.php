<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Custom route for user actions.
$route['tour_guide/user/(:any)'] = 'tour_guide_common/user/$1';

$route['tour_guide/admin/setup/(:any)'] = 'tour_guide_common/setup/$1';
$route['tour_guide/admin/setup/(:any)/(:any)'] = 'tour_guide_common/setup/$1/$2';

$route['tour_guide/admin'] = 'tour_guide/index';
$route['tour_guide/admin/(:any)'] = 'tour_guide/$1';
$route['tour_guide/admin/(:any)/(:any)'] = 'tour_guide/$1/$2';
$route['tour_guide/admin/(:any)/(:any)/(:any)'] = 'tour_guide/$1/$2/$3';