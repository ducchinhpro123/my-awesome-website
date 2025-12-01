<?php

namespace MyAwesomeWebsite\controller;

use MyAwesomeWebsite\Controller;
use MyAwesomeWebsite\repository\CartRepository;
use MyAwesomeWebsite\repository\CartItemRepository;
use MyAwesomeWebsite\repository\OrderRepository;
use MyAwesomeWebsite\repository\UserRepository;
use MyAwesomeWebsite\service\PaymentService;
use MyAwesomeWebsite\model\Order;
use MyAwesomeWebsite\model\OrderItem;

class OrderController extends Controller
{
    private CartRepository $cartRepository;
    private CartItemRepository $cartItemRepository;
    private OrderRepository $orderRepository;
    private UserRepository $userRepository;
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->cartRepository = new CartRepository();
        $this->cartItemRepository = new CartItemRepository();
        $this->orderRepository = new OrderRepository();
        $this->userRepository = new UserRepository();
        $this->paymentService = new PaymentService();
        parent::__construct();
    }

    /**
     * Display checkout page with order summary
     */
    public function checkout()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /?action=login");
            exit;
        }

        $cart = $this->cartRepository->getCart();
        
        if (empty($cart) || $cart->getCartItems()->isEmpty()) {
            $_SESSION['error'] = 'Your cart is empty';
            header("Location: /?action=cart");
            exit;
        }

        $cartItems = $cart->getCartItems();
        
        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $cartItem) {
            $subtotal += floatval($cartItem->getProduct()->getPrice()) * $cartItem->getQuantity();
        }
        
        $tax = $subtotal * 0.08; // 8% tax
        $shipping = $subtotal >= 50 ? 0 : 5.99;
        $total = $subtotal + $tax + $shipping;

        $this->args['cart'] = $cart;
        $this->args['cartItems'] = $cartItems;
        $this->args['subtotal'] = number_format($subtotal, 2);
        $this->args['tax'] = number_format($tax, 2);
        $this->args['shipping'] = number_format($shipping, 2);
        $this->args['total'] = number_format($total, 2);
        
        print $this->twig->render('checkout.html.twig', $this->args);
    }

    /**
     * Process payment and create order
     */
    public function processPayment()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /?action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /?action=checkout");
            exit;
        }

        $cart = $this->cartRepository->getCart();
        
        if (empty($cart) || $cart->getCartItems()->isEmpty()) {
            $_SESSION['error'] = 'Your cart is empty';
            header("Location: /?action=cart");
            exit;
        }

        // Calculate total amount
        $cartItems = $cart->getCartItems();
        $subtotal = 0;
        foreach ($cartItems as $cartItem) {
            $subtotal += floatval($cartItem->getProduct()->getPrice()) * $cartItem->getQuantity();
        }
        
        $tax = $subtotal * 0.08;
        $shipping = $subtotal >= 50 ? 0 : 5.99;
        $total = $subtotal + $tax + $shipping;

        // Process payment through payment service (Instant Checkout)
        $paymentResult = $this->paymentService->processPayment(
            'instant_checkout',
            number_format($total, 2, '.', ''),
            []
        );

        // Handle payment result
        if ($paymentResult['status'] === PaymentService::STATUS_SUCCESS) {
            // Create order
            $user = $this->userRepository->find($_SESSION['user_id']);
            $order = $this->orderRepository->createOrder($user, number_format($total, 2, '.', ''));
            
            // Create order items from cart items
            foreach ($cartItems as $cartItem) {
                $orderItem = new OrderItem(
                    $order,
                    $cartItem->getProduct(),
                    $cartItem->getQuantity(),
                    $cartItem->getProduct()->getPrice()
                );
                $order->addOrderItem($orderItem);
            }
            
            // Update order status to paid
            $order->setStatus(Order::STATUS_PAID);
            $this->orderRepository->save($order);

            // Clear the cart
            foreach ($cartItems as $cartItem) {
                $this->cartItemRepository->remove($cartItem->getProduct()->getId(), $cart->getId());
            }

            // Store payment result in session for display
            $_SESSION['payment_result'] = $paymentResult;
            $_SESSION['order_id'] = $order->getId();
            
            header("Location: /?action=payment-result");
            exit;
        } else {
            // Payment failed
            $_SESSION['payment_result'] = $paymentResult;
            header("Location: /?action=payment-result");
            exit;
        }
    }

    /**
     * Display payment result page
     */
    public function paymentResult()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /?action=login");
            exit;
        }

        if (!isset($_SESSION['payment_result'])) {
            header("Location: /?action=cart");
            exit;
        }

        $paymentResult = $_SESSION['payment_result'];
        $orderId = $_SESSION['order_id'] ?? null;
        
        $this->args['payment_result'] = $paymentResult;
        $this->args['order_id'] = $orderId;
        
        // Clear payment result from session
        unset($_SESSION['payment_result']);
        unset($_SESSION['order_id']);
        
        print $this->twig->render('payment-result.html.twig', $this->args);
    }
}