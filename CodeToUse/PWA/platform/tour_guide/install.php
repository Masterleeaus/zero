<?php

defined('BASEPATH') or exit('No direct script access allowed');

\TourGuide\TourGuideInstaller::install();

tourGuideHelper()->publishAssets();