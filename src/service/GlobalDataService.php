<?php

namespace MyAwesomeWebsite\service;

use MyAwesomeWebsite\repository\CategoryRepository;
use MyAwesomeWebsite\repository\CartRepository;
use MyAwesomeWebsite\repository\UserRepository;

class GlobalDataService
{
    private static $instance = null;
    private CategoryRepository $categoryRepository;
    private CartRepository $cartRepository;
    private UserRepository $userRepository;

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
        $this->userRepository = new UserRepository();
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

            if (isset($_SESSION['user_id'])) {
                $numCartItems = $this->cartRepository->getCartNumber();
                $data['numCartItems'] = $numCartItems;
            }

        } catch (\Exception $e) {
            $data['categories'] = [];
            $data['categoriesFeatured'] = [];
            $data['numCartItems'] = 0;
        }

        if (isset($_SESSION['username'])) {
            $data['username'] = $_SESSION['username'];
        }

        if (isset($_SESSION['user_id'])) {
            $user = $this->userRepository->find($_SESSION['user_id']);
            if ($user) {
                $data['user'] = $user;
            }
        }
        return $data;
    }
}

?>
