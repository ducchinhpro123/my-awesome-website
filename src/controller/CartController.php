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

    private function getCart()
    {
        if (!$_SESSION['user']) {
            header("Location: /?action=login");
            exit;
        }

        $userId = $_SESSION['user']->getId();
        $template = 'cart.html.twig';
        $cart = $this->cartRepository->findOneBy(["user" => $userId]);
        return $cart;
    }

    public function getCartNumber()
    {
        $cart = $this->getCart();
        return count($cart->getCartItems());
    }


    public function cart()
    {
        if (!isset($_SESSION['user_id']) || !$_SESSION['user']) {
            header("Location: /?action=login");
            exit;
        }
        $template = 'cart.html.twig';

        $cart = $this->getCart();

        if(empty($cart)) {
            $cart = $this->cartRepository->newCart();
        }

        $cartItems = $cart->getCartItems();

        $this->args['cart'] = $cart;
        $this->args['cartItems'] = $cartItems;

        print $this->twig->render($template, $this->args);
    }

    public function addToCart($product_id)
    {
        $product = $this->productRepository->find($product_id);
        $cart = $this->getCart();

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
