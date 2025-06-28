<?php

namespace MyAwesomeWebsite\service;

use MyAwesomeWebsite\repository\CategoryRepository;
use MyAwesomeWebsite\repository\CartRepository;

class GlobalDataService
{
    private static $instance = null;
    private CategoryRepository $categoryRepository;
    private CartRepository $cartRepository;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
        $this->cartRepository = new CartRepository();
    }

    /*
     * The data that will be needed by every page of the web
     * */
    public function getGlobalTemplateData()
    {
        $data = [];
        try {
            $data['categories'] = $this->categoryRepository->findAll();
            $data['categoriesFeatured'] = $this->categoryRepository->getCategoriesFeatured();

            if (isset($_SESSION['user'])) {
                $numCartItems = $this->cartRepository->getCartNumber();
                $data['numCartItems'] = $numCartItems;
            }

        } catch (\Exception $e) {
            $data['categories'] = [];
            $data['categoriesFeatured'] = [];
            $data['numCartItems'] = 0;
        }

        if (isset($_SESSION['user'])) {
            $data['user'] = $_SESSION['user'];
        }

        return $data;
    }

}

?>
