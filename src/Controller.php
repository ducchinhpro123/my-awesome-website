<?php
namespace MyAwesomeWebsite;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

abstract class Controller
{
    const PATH_TO_TEMPLATES = __DIR__ . '/../templates';

    protected environment $twig;
    protected array $args = [];

    public function __construct()
    {
        $loader = new FilesystemLoader(self::PATH_TO_TEMPLATES);
        $this->twig = new Environment($loader);
        if (isset($_SESSION['username'])) {
            $this->args['username'] = $_SESSION['username'];
        }
    }
}

?>
