<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../wp-load.php';

use Controller\BaseController as base;
base::checkSubscription();