<?php

namespace MyAwesomeWebsite\controller;

use MyAwesomeWebsite\repository\ProductRepository;
use MyAwesomeWebsite\repository\CartItemRepository;
use MyAwesomeWebsite\Controller;


class CartItemController extends Controller
{
    private ProductRepository $productRepository;
    private CartItemRepository $cartItemRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->cartItemRepository = new CartItemRepository();
        parent::__construct();
    }
}
