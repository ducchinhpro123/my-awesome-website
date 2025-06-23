<?php

namespace MyAwesomeWebsite;

use MyAwesomeWebsite\DefaultController;

class Application
{
    private DefaultController $defaultController;
    private ProductController $productController;

    public function __construct()
    {
        $this->defaultController = new DefaultController();
        $this->productController = new ProductController();
    }

    public function run()
    {
        $action = filter_input(INPUT_GET, 'action');
        switch($action)
        {
            case 'home':
                $this->defaultController->homePage();
                break;
            case 'products':
                $this->productController->productsPage();
                break;
            case 'product-detail':
                $this->productController->productDetailPage();
                break;
            default:
                $this->defaultController->homePage();
        }
    }
}

?>
