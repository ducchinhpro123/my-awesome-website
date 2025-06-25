<?php

namespace MyAwesomeWebsite;

class DefaultController extends Controller
{
    public function homePage()
    {
        $template = 'home.html.twig';
        print $this->twig->render($template, $this->args);
    }

}

?>
