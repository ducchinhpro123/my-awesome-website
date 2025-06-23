<?php

namespace MyAwesomeWebsite;

use MyAwesomeWebsite\DefaultController;

class Application
{
    private DefaultController $defaultController;

    public function __construct()
    {
        $this->defaultController = new DefaultController();
    }

    public function run()
    {
        $action = filter_input(INPUT_GET, 'action');
        switch($action)
        {
            default:
                $this->defaultController->homePage();
        }
    }
}

?>
