<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\View;
use App\Core\Router;

define('APP_PATH', __DIR__);

$loader = new \Twig\Loader\FilesystemLoader(APP_PATH . '/app/Views');
$twig = View::init($loader);

$router = new Router();

$router->get('/', 'LivroController@index');
$router->get('/livros/cadastrar', 'LivroController@create');
$router->post('/livros', 'LivroController@store');
$router->get('/livros/editar/{id}', 'LivroController@edit');
$router->post('/livros/atualizar/{id}', 'LivroController@update');
$router->post('/livros/excluir/{id}', 'LivroController@destroy');

$router->dispatch();
