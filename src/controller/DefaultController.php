<?php

namespace MyAwesomeWebsite\controller;
use MyAwesomeWebsite\Controller;

class DefaultController extends Controller
{
    public function homePage()
    {
        $template = 'home.html.twig';
        print $this->twig->render($template, $this->args);
    }

}

?>
