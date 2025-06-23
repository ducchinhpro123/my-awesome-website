<?php
namespace MyAwesomeWebsite;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

abstract class Controller
{
    const PATH_TO_TEMPLATES = __DIR__ . '/../templates';

    protected Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(self::PATH_TO_TEMPLATES);
        $this->twig = new Environment($loader);
    }
}

?>
