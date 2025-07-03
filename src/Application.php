<?php

namespace MyAwesomeWebsite;

use MyAwesomeWebsite\controller\DefaultController;
use MyAwesomeWebsite\controller\ProductController;
use MyAwesomeWebsite\controller\CartController;
use MyAwesomeWebsite\controller\UserController;

class Application
{
    private DefaultController $defaultController;
    private ProductController $productController;
    private CartController $cartController;
    private UserController $userController;

    public function __construct()
    {
        $this->defaultController = new DefaultController();
        $this->productController = new ProductController();
        $this->userController = new UserController();
        $this->cartController = new CartController();
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
            case 'profile':
                $this->userController->profilePage();
                break;
            case 'orders':
                $this->userController->orderPage();
                break;
            case 'wishlist':
                $this->userController->wishlistPage();
                break;
            case 'logout':
                $this->userController->logout();
                break;

            case 'cart':
            case 'remove-cart-item':
                $this->cartController->cart();
                break;

            case 'updateProfile':
                $isSubmitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

                if ($isSubmitted) {
                    $firstName = filter_input(INPUT_POST, 'firstName');
                    $lastName = filter_input(INPUT_POST, 'lastName');

                    $this->userController->updateProfile($firstName, $lastName);
                } else {
                    $this->userController->profilePage();
                }
                break;

            case 'add-to-cart':
                $product_id = filter_input(INPUT_GET, 'product_id');
                $this->cartController->addToCart($product_id);
                break;
            case 'products-filter':
                $this->productController->productsFilter();
                break;
            /* case 'products-search': */
            /*     $this->productController->searchProducts(); */
            /*     break; */

            default:
                $this->defaultController->homePage();
        }
    }
}

?>
