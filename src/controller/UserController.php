<?php

namespace MyAwesomeWebsite\controller;

use MyAwesomeWebsite\model\User;
use MyAwesomeWebsite\repository\UserRepository;
use MyAwesomeWebsite\Controller;

class UserController extends Controller
{
    private UserRepository $userRepository;
    private CartController $cartController;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->cartController = new CartController();
        parent::__construct();
    }


    public function loginPage(): void
    {
        if (isset($_SESSION['user'])) {
            header("Location: /");
            exit;
        }
        $err = filter_input(INPUT_GET, 'err', FILTER_DEFAULT);
        if (!empty($err)) {
            $this->args['errorMessage'] = "Thông tin không hợp lệ, quý khách vui lòng kiểm tra lại!";
        }

        $template = 'login.html.twig';
        print $this->twig->render($template, $this->args);
    }

    public function loginProcess(): void
    {
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            header("Location: /?action=login&err=1");
            exit;
        }
        if (password_verify($password, $user->getPassword())) {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();

            if (isset($_SESSION['post_login_action'])) {
                $pendingAction = $_SESSION['post_login_action'];
                unset($_SESSION['post_login_action']);

                if ($pendingAction['action'] === 'add_to_cart' && !empty($pendingAction['productId'])) {
                    $this->cartController->addToCart($pendingAction['productId']);
                    exit;
                }
            }

            $location = '/';
            header("Location: $location");
        } else {
            header("Location: /?action=login&err=1");
        }
    }

    public function registerPage(): void
    {
        $template = 'register.html.twig';

        if (isset($_SESSION['registration_errors'])) {
            $this->args['errors'] = $_SESSION['registration_errors'];
            unset($_SESSION['registration_errors']);
        }

        if (isset($_SESSION['registration_data'])) {
            $this->args['formData'] = $_SESSION['registration_data'];
            unset($_SESSION['registration_data']);
        }

        print $this->twig->render($template, $this->args);
    }

    public function registerProcess(): void
    {
        $firstName = filter_input(INPUT_POST, 'firstName');
        $lastName = filter_input(INPUT_POST, 'lastName');
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        $phoneNumber = filter_input(INPUT_POST, 'phone');

        $errors = [];

        if (empty($firstName)) {
            $errors[] = "Họ không được để trống";
        }
        if (empty($lastName)) {
            $errors[] = "Tên không được để trống";
        }
        if (empty($username)) {
            $errors[] = "Tên đăng nhập không được để trống";
        } elseif (strlen($username) < 5) {
            $errors[] = "Tên đăng nhập phải có ít nhất 5 ký tự";
        }
        if (empty($password)) {
            $errors[] = "Mật khẩu không được để trống";
        } elseif (strlen($password) < 8) {
            $errors[] = "Mật khẩu phải có ít nhất 8 ký tự";
        }
        /* if ($password !== $confirmPassword) { */
        /*     $errors[] = "Mật khẩu xác nhận không khớp"; */
        /* } */
        if (empty($phoneNumber)) {
            $errors[] = "Số điện thoại không được để trống";
        } elseif (!preg_match('/^[0-9]{10,11}$/', $phoneNumber)) {
            $errors[] = "Số điện thoại không hợp lệ";
        }

        // Check if username is already exists
        $existUsername = $this->userRepository->findOneBy(['username' => $username]);
        if ($existUsername) {
            $errors[] = "Tên đăng nhập đã tồn tại";
        }
        if (!empty($errors)) {
            $_SESSION['registration_errors'] = $errors;
            $_SESSION['registration_data'] = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'username' => $username,
                'phoneNumber' => $phoneNumber,
                'password' => $password
            ];
            header("Location: /?action=register");
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($username, $hashedPassword, $firstName, $lastName, $phoneNumber);
        $this->userRepository->create($user);
        $location = "/?action=login";
        header("Location: $location");
    }

    public function profilePage(): void
    {
        $template = 'profile.html.twig';

        if (isset($_SESSION['user_id'])) {
            $user = $this->userRepository->find($_SESSION['user_id']);
            if ($user) {
                $this->args['user'] = $user;
                print $this->twig->render($template, $this->args);
            } else {
                $location = '/?action=login';
                header("Location: $location");
            }
        } else {
            $location = '/?action=login';
            header("Location: $location");
        }
    }

    public function orderPage(): void
    {
        $template = 'orders.html.twig';
        print $this->twig->render($template, $this->args);

    }

    public function wishlistPage(): void
    {
        $template = 'wishlist.html.twig';
        print $this->twig->render($template, $this->args);
    }

    public function logout(): void
    {
        $_SESSION = [];
        $location = "/";
        header("Location: $location");
    }
    /**
     * @param mixed $firstName
     * @param mixed $lastName
     */
    public function updateProfile($firstName, $lastName): void
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
        }

        $user_id = $_SESSION['user_id'];
        $user = $this->userRepository->find($user_id);

        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $this->userRepository->create($user);

        $location = '/?action=profile';
        header("Location: $location");
    }

}
