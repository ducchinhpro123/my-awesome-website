<?php

namespace MyAwesomeWebsite;

use MyAwesomeWebsite\DefaultController;

class Application
{
    private DefaultController $defaultController;
    private ProductController $productController;
    private UserController $userController;

    public function __construct()
    {
        $this->defaultController = new DefaultController();
        $this->productController = new ProductController();
        $this->userController = new UserController();
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
                $id = filter_input(INPUT_GET, 'id');
                if (empty($id)) {
                    // TODO: Navigate to error page
                    $this->defaultController->homePage();
                } else {
                    $this->productController->productDetailPage($id);
                }
                break;
            case 'login':
                $this->userController->loginPage();
                break;
            case 'login-process':
                $isSubmitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
                if ($isSubmitted) {
                    $this->userController->loginProcess();
                    break;
                } else {
                    $this->userController->loginPage();
                }
            case 'register':
                $this->userController->registerPage();
                break;
            case 'register-process':
                $this->userController->registerProcess();
                break;

            default:
                $this->defaultController->homePage();
        }
    }
}

?>
