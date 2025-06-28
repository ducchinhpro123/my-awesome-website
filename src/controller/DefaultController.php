<?php

namespace MyAwesomeWebsite\controller;

use MyAwesomeWebsite\controller\CategoryController;
use MyAwesomeWebsite\controller\CartController;
use MyAwesomeWebsite\Controller;

class DefaultController extends Controller
{

    private CategoryController $categoryController;
    private CartController $cartController;

    public function __construct()
    {
        $this->categoryController = new CategoryController();
        $this->cartController = new CartController();
        parent::__construct();
    }

    public function homePage()
    {
        $categoriesFeatured = $this->categoryController->getCategoriesFeatured();
        $numCartItems = $this->cartController->getCartNumber();
        $categories = $this->categoryController->getCategories();

        $this->args['categoriesFeatured'] = $categoriesFeatured;
        $this->args['numCartItems'] = $numCartItems;
        $this->args['categories'] = $categories;

        $template = 'home.html.twig';

        print $this->twig->render($template, $this->args);
    }

}

?>
