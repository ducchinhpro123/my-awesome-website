<?php

namespace MyAwesomeWebsite\controller;

use MyAwesomeWebsite\repository\CategoryRepository;
use MyAwesomeWebsite\Controller;

class CategoryController extends Controller
{
    private CategoryRepository $categoryRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
        parent::__construct();
    }

    public function getCategoriesFeatured()
    {
        return $this->categoryRepository->getCategoriesFeatured();
    }

    public function getCategories()
    {
        return $this->categoryRepository->findAll();
    }
}

?>
