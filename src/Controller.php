<?php

namespace MyAwesomeWebsite;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

use MyAwesomeWebsite\service\GlobalDataService;

abstract class Controller
{
    const PATH_TO_TEMPLATES = __DIR__ . '/../templates';

    protected environment $twig;
    protected array $args = [];

    public function __construct()
    {
        $loader = new FilesystemLoader(self::PATH_TO_TEMPLATES);
        $this->twig = new Environment($loader);

        $globalData = GlobalDataService::getInstance()->getGlobalTemplateData();
        $this->args = array_merge($this->args, $globalData);
    }
}
