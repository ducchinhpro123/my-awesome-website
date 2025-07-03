<?php

namespace MyAwesomeWebsite\controller;

use MyAwesomeWebsite\model\CartItem;
use MyAwesomeWebsite\repository\ProductRepository;
use MyAwesomeWebsite\repository\CartRepository;
use MyAwesomeWebsite\repository\CartItemRepository;
use MyAwesomeWebsite\Controller;

class CartController extends Controller
{
    private ProductRepository $productRepository;
    private CartRepository $cartRepository;
    private CartItemRepository $cartItemRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->cartRepository = new CartRepository();
        $this->cartItemRepository = new CartItemRepository();
        parent::__construct();
    }

    public function getCart()
    {
        $cart = $this->cartRepository->getCart();
        return $cart;
    }

    public function cart()
    {
        if (!isset($_SESSION['user_id']) || !$_SESSION['user']) {
            header("Location: /?action=login");
            exit;
        }
        $template = 'cart.html.twig';

        $cart = $this->cartRepository->getCart();

        if(empty($cart)) {
            $cart = $this->cartRepository->newCart();
        }

        $productId = filter_input(INPUT_GET, 'product_id_delete', FILTER_DEFAULT, FILTER_VALIDATE_INT);
        if (!empty($productId)) {
            $this->cartItemRepository->remove($productId, $cart->getId());
            header("Location: /?action=cart");
            exit;
        }

        $cartItems = $cart->getCartItems();

        $this->args['cart'] = $cart;
        $this->args['cartItems'] = $cartItems;

        print $this->twig->render($template, $this->args);
    }

    public function addToCart($product_id)
    {
        error_log('got product id: ' . $product_id);
        $product = $this->productRepository->find($product_id);
        $cart = $this->cartRepository->getCart();

        if(empty($cart)) {
            $cart = $this->cartRepository->newCart();
        }

        $existingCartItem = null;
        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getProduct()->getId() == $product_id) {
                $existingCartItem = $cartItem;
                break;
            }
        }

        if ($existingCartItem) {
            $existingCartItem->setQuantity($existingCartItem->getQuantity() + 1);
            $this->cartItemRepository->save($existingCartItem);
        } else {
            $newCartItem = new CartItem();
            $newCartItem->setProduct($product);
            $newCartItem->setQuantity(1);
            $cart->addCartItem($newCartItem);

            $this->cartItemRepository->save($newCartItem);
            $this->cartRepository->save($cart);
        }

        header("Location: /?action=cart");
        exit;
    }
}


?>
