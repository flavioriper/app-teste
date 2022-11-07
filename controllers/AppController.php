<?php

namespace Controller;

use Flight;
use Controller\BaseController as base;
use Controller\AuthenticationController as auth;
use stdClass;

class AppController extends BaseController {

    public static function page() {

        self::authorization();
        self::subscription();
        Flight::redirect(base::get_menu()[0]['link']);
        
    } 

}