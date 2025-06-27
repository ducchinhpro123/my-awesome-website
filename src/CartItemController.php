<?php

namespace MyAwesomeWebsite;


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


?>
