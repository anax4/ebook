<?php

namespace App\Core;

class Controller
{
    protected $twig;
    protected $data = [];

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    protected function view($template, $data = [])
    {
        $data = array_merge($this->data, $data);

        echo $this->twig->render($template, $data);
    }

    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    protected function back()
    {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
}
