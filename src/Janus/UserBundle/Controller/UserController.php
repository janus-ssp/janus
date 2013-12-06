<?php

namespace Janus\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

class UserController extends  FOSRestController
{
    public function indexAction($name)
    {
        return array('name' => $name);
    }
}
