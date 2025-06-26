<?php

namespace MyAwesomeWebsite;


class CartController extends Controller
{
    public function cart()
    {
        $template = 'cart.html.twig';
        print $this->twig->render($template, $this->args);
    }
}


?>
