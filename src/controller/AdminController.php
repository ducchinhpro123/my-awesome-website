<?php

namespace MyAwesomeWebsite\controller;

use Cloudinary\Api\Upload\UploadApi;
use MyAwesomeWebsite\Controller;
use MyAwesomeWebsite\model\User;
use MyAwesomeWebsite\model\Product;
use MyAwesomeWebsite\repository\UserRepository;
use MyAwesomeWebsite\repository\OrderRepository;
use MyAwesomeWebsite\repository\ProductRepository;
use MyAwesomeWebsite\repository\CategoryRepository;

class AdminController extends Controller
{
    private UserRepository $userRepository;
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private CategoryRepository $categoryRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->orderRepository = new OrderRepository();
        $this->productRepository = new ProductRepository();
        $this->categoryRepository = new CategoryRepository();
        parent::__construct();
    }

    /**
     * Check if user is admin, redirect if not
     */
    private function requireAdmin(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /?action=login");
            exit;
        }

        $user = $this->userRepository->find($_SESSION['user_id']);
        
        if (!$user || !$user->isAdmin()) {
            $_SESSION['error'] = 'Access denied. Admin privileges required.';
            header("Location: /");
            exit;
        }

        return $user;
    }

    /**
     * Admin Dashboard - main page with stats
     */
    public function dashboard()
    {
        $admin = $this->requireAdmin();

        // Gather statistics
        $stats = [
            'total_users' => $this->userRepository->countUsers(),
            'total_orders' => $this->orderRepository->countOrders(),
            'total_products' => $this->productRepository->getTotalProductsNumber(),
            'total_revenue' => $this->orderRepository->getTotalRevenue(),
            'pending_orders' => $this->orderRepository->countByStatus('pending'),
            'paid_orders' => $this->orderRepository->countByStatus('paid'),
        ];

        // Recent orders
        $recentOrders = $this->orderRepository->getRecentOrders(5);

        // Recent users
        $recentUsers = $this->userRepository->getAllUsers(0, 5);

        $this->args['admin'] = $admin;
        $this->args['stats'] = $stats;
        $this->args['recent_orders'] = $recentOrders;
        $this->args['recent_users'] = $recentUsers;
        $this->args['page_title'] = 'Dashboard';

        print $this->twig->render('admin/dashboard.html.twig', $this->args);
    }

    /**
     * Users management page
     */
    public function users()
    {
        $admin = $this->requireAdmin();

        $page = (int) filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 0;
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($search) {
            $users = $this->userRepository->searchUsers($search);
        } else {
            $users = $this->userRepository->getAllUsers($page, 20);
        }

        $totalUsers = $this->userRepository->countUsers();

        $this->args['admin'] = $admin;
        $this->args['users'] = $users;
        $this->args['total_users'] = $totalUsers;
        $this->args['current_page'] = $page;
        $this->args['search'] = $search;
        $this->args['page_title'] = 'Users Management';

        print $this->twig->render('admin/users.html.twig', $this->args);
    }

    /**
     * Update user role
     */
    public function updateUserRole()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /?action=admin-users");
            exit;
        }

        $userId = (int) filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$userId || !in_array($role, [User::ROLE_USER, User::ROLE_ADMIN])) {
            $_SESSION['error'] = 'Invalid request';
            header("Location: /?action=admin-users");
            exit;
        }

        // Prevent admin from changing their own role
        if ($userId === $_SESSION['user_id']) {
            $_SESSION['error'] = 'Cannot change your own role';
            header("Location: /?action=admin-users");
            exit;
        }

        $user = $this->userRepository->find($userId);
        if ($user) {
            $user->setRole($role);
            $this->userRepository->save($user);
            $_SESSION['success'] = 'User role updated successfully';
        }

        header("Location: /?action=admin-users");
        exit;
    }

    /**
     * Delete user
     */
    public function deleteUser()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /?action=admin-users");
            exit;
        }

        $userId = (int) filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

        // Prevent admin from deleting themselves
        if ($userId === $_SESSION['user_id']) {
            $_SESSION['error'] = 'Cannot delete your own account';
            header("Location: /?action=admin-users");
            exit;
        }

        if ($this->userRepository->delete($userId)) {
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }

        header("Location: /?action=admin-users");
        exit;
    }

    /**
     * Orders management page
     */
    public function orders()
    {
        $admin = $this->requireAdmin();

        $page = (int) filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 0;
        $orders = $this->orderRepository->getAllOrders($page, 20);
        $totalOrders = $this->orderRepository->countOrders();

        $this->args['admin'] = $admin;
        $this->args['orders'] = $orders;
        $this->args['total_orders'] = $totalOrders;
        $this->args['current_page'] = $page;
        $this->args['page_title'] = 'Orders Management';

        print $this->twig->render('admin/orders.html.twig', $this->args);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /?action=admin-orders");
            exit;
        }

        $orderId = (int) filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $validStatuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!$orderId || !in_array($status, $validStatuses)) {
            $_SESSION['error'] = 'Invalid request';
            header("Location: /?action=admin-orders");
            exit;
        }

        if ($this->orderRepository->updateStatus($orderId, $status)) {
            $_SESSION['success'] = 'Order status updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update order status';
        }

        header("Location: /?action=admin-orders");
        exit;
    }

    /**
     * Products management page
     */
    public function products()
    {
        $admin = $this->requireAdmin();

        $page = (int) filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 0;
        $products = $this->productRepository->getProductsPagination($page);
        $totalProducts = $this->productRepository->getTotalProductsNumber();
        $categories = $this->categoryRepository->findAll();

        $this->args['admin'] = $admin;
        $this->args['products'] = $products;
        $this->args['categories'] = $categories;
        $this->args['total_products'] = $totalProducts;
        $this->args['current_page'] = $page;
        $this->args['page_title'] = 'Products Management';

        print $this->twig->render('admin/products.html.twig', $this->args);
    }

    /**
     * Toggle user role between admin and user
     */
    public function toggleUserRole()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /?action=admin-users");
            exit;
        }

        $userId = (int) filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

        if (!$userId) {
            $_SESSION['error'] = 'Invalid user ID';
            header("Location: /?action=admin-users");
            exit;
        }

        // Prevent admin from changing their own role
        if ($userId === $_SESSION['user_id']) {
            $_SESSION['error'] = 'Cannot change your own role';
            header("Location: /?action=admin-users");
            exit;
        }

        $user = $this->userRepository->find($userId);
        if ($user) {
            // Toggle role
            if ($user->isAdmin()) {
                $user->setIsAdmin(false);
                $_SESSION['success'] = 'User demoted to regular user';
            } else {
                $user->setIsAdmin(true);
                $_SESSION['success'] = 'User promoted to admin';
            }
            $this->userRepository->save($user);
        } else {
            $_SESSION['error'] = 'User not found';
        }

        header("Location: /?action=admin-users");
        exit;
    }

    /**
     * Delete a product
     */
    public function deleteProduct()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /?action=admin-products");
            exit;
        }

        $productId = (int) filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if (!$productId) {
            $_SESSION['error'] = 'Invalid product ID';
            header("Location: /?action=admin-products");
            exit;
        }

        if ($this->productRepository->deleteProduct($productId)) {
            $_SESSION['success'] = 'Product deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete product';
        }

        header("Location: /?action=admin-products");
        exit;
    }

    /**
     * Add a new product
     */
    public function addProduct()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /?action=admin-products");
            exit;
        }

        // Get form data
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $categoryId = (int) filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $stock = (int) filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        $rating = (int) filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

        // Validate required fields
        if (empty($name) || $price === false || !$categoryId) {
            $_SESSION['error'] = 'Please fill in all required fields (Name, Price, Category)';
            header("Location: /?action=admin-products");
            exit;
        }

        // Get category
        $category = $this->categoryRepository->find($categoryId);
        if (!$category) {
            $_SESSION['error'] = 'Invalid category selected';
            header("Location: /?action=admin-products");
            exit;
        }

        // Handle image upload
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $upload = new UploadApi();
                $result = $upload->upload($_FILES['image']['tmp_name'], [
                    'folder' => 'products',
                    'use_filename' => true,
                    'overwrite' => true
                ]);
                if (isset($result['secure_url'])) {
                    $imageUrl = $result['secure_url'];
                }
            } catch (\Exception $e) {
                // Image upload failed, but continue with product creation
                $_SESSION['warning'] = 'Product created but image upload failed: ' . $e->getMessage();
            }
        }

        // Create the product
        try {
            $this->productRepository->createProduct(
                $name,
                $price,
                $category,
                $rating ?: 0,
                $description ?: null,
                $imageUrl,
                $stock ?: 0
            );
            $_SESSION['success'] = 'Product added successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to add product: ' . $e->getMessage();
        }

        header("Location: /?action=admin-products");
        exit;
    }

    /**
     * Edit an existing product
     */
    public function editProduct()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /?action=admin-products");
            exit;
        }

        // Get form data
        $productId = (int) filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $categoryId = (int) filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $stock = (int) filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        $rating = (int) filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

        // Validate required fields
        if (!$productId || empty($name) || $price === false || !$categoryId) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header("Location: /?action=admin-products");
            exit;
        }

        // Get the product
        $product = $this->productRepository->find($productId);
        if (!$product) {
            $_SESSION['error'] = 'Product not found';
            header("Location: /?action=admin-products");
            exit;
        }

        // Get category
        $category = $this->categoryRepository->find($categoryId);
        if (!$category) {
            $_SESSION['error'] = 'Invalid category selected';
            header("Location: /?action=admin-products");
            exit;
        }

        // Handle image upload if new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $upload = new UploadApi();
                $result = $upload->upload($_FILES['image']['tmp_name'], [
                    'folder' => 'products',
                    'use_filename' => true,
                    'overwrite' => true,
                    'public_id' => 'product_' . $productId
                ]);
                if (isset($result['secure_url'])) {
                    $product->setImageUrl($result['secure_url']);
                }
            } catch (\Exception $e) {
                $_SESSION['warning'] = 'Product updated but image upload failed: ' . $e->getMessage();
            }
        }

        // Update product fields
        $product->setName($name);
        $product->setPrice($price);
        $product->setCategory($category);
        $product->setDescription($description ?: null);
        $product->setStock($stock ?: 0);
        $product->setRating($rating ?: 0);

        // Save the product
        try {
            $this->productRepository->save($product);
            $_SESSION['success'] = 'Product updated successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to update product: ' . $e->getMessage();
        }

        header("Location: /?action=admin-products");
        exit;
    }
}
