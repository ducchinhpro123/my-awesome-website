<?php

namespace MyAwesomeWebsite;

use MyAwesomeWebsite\model\User;

class UserController extends Controller
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        parent::__construct();
    }

    public function loginPage()
    {
        $template = 'login.html.twig';
        print $this->twig->render($template, $this->args);
    }

    public function loginProcess()
    {
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        $users = $this->userRepository->findBy(['username' => $username]);
 
        $user = $users[0];

        if(!$user) {
            $template = 'login.html.twig';
            print $this->twig->render($template, $this->args);
        }
        if(password_verify($password, $user->getPassword())) {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user'] = $user;

            $location = '/';
            header("Location: $location");
        } else {
            $location = '/action=login';
            header("Location: $location");
        }
    }

    public function registerPage()
    {
        $template = 'register.html.twig';
        print $this->twig->render($template, $this->args);
    }

    public function registerProcess()
    {
        $firstName = filter_input(INPUT_POST, 'firstName');
        $lastName = filter_input(INPUT_POST, 'lastName');
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        $phoneNumber = filter_input(INPUT_POST, 'phone');

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $user = new User($username, $hashedPassword, $firstName, $lastName, $phoneNumber);

        $this->userRepository->create($user);

        $location = "/?action=login";
        header("Location: $location");
    }

    public function profilePage()
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

    public function orderPage()
    {
        $template = 'orders.html.twig';
        print $this->twig->render($template, $this->args);

    }

    public function wishlistPage()
    {
        $template = 'wishlist.html.twig';
        print $this->twig->render($template, $this->args);
    }

    public function logout()
    {
        $_SESSION = [];
        $location = "/";
        header("Location: $location");
    }

    public function updateProfile($firstName, $lastName)
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

?>
