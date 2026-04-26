<?php

namespace App\Core;

class View
{
    private static $twig;

    public static function init($loader)
    {
        self::$twig = new \Twig\Environment($loader, [
            'cache' => false,
            'auto_reload' => true,
            'strict_variables' => false,
            'autoescape' => 'html',
        ]);

        self::$twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
            return $path;
        }));

        return self::$twig;
    }

    public static function getTwig()
    {
        return self::$twig;
    }
}
