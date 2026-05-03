<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\View;
use App\Core\Router;

define('APP_PATH', __DIR__);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

ini_set('default_charset', 'UTF-8');

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

set_exception_handler(function (\Throwable $exception): void {
    error_log((string) $exception);

    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    http_response_code(500);

    if (strpos($uri, '/api/') === 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'internal_error',
            'message' => 'ão foi possível processar a solicitação.',
        ]);
        return;
    }

    echo '500 - Erro interno do servidor';
});

$loader = new \Twig\Loader\FilesystemLoader(APP_PATH . '/app/Views');
$twig = View::init($loader);

$router = new Router();

$router->get('/', 'LivroController@index');
$router->get('/autores/cadastrar', 'AutorController@create');
$router->post('/autores', 'AutorController@store');
$router->get('/autores/editar/{id}', 'AutorController@edit');
$router->post('/autores/atualizar/{id}', 'AutorController@update');
$router->post('/autores/excluir/{id}', 'AutorController@delete');
$router->get('/assuntos/cadastrar', 'AssuntoController@create');
$router->get('/assuntos/editar/{id}', 'AssuntoController@edit');
$router->post('/assuntos/atualizar/{id}', 'AssuntoController@update');
$router->post('/assuntos', 'AssuntoController@store');
$router->post('/assuntos/excluir/{id}', 'AssuntoController@delete');
$router->get('/livros/cadastrar', 'LivroController@create');
$router->post('/livros', 'LivroController@store');
$router->get('/livros/editar/{id}', 'LivroController@edit');
$router->post('/livros/atualizar/{id}', 'LivroController@update');
$router->post('/livros/excluir/{id}', 'LivroController@destroy');
$router->get('/relatorios', 'RelatorioController@index');
$router->get('/api/relatorio-livros', 'RelatorioLivrosApiController@index');

$router->dispatch();
