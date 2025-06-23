<?php

namespace MyAwesomeWebsite;

class DefaultController extends Controller
{
    public function homePage()
    {
        $template = 'home.html.twig';
        $args = [];
        print $this->twig->render($template, $args);
    }
}

?>
