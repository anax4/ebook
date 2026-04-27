<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\RelatorioLivrosService;

class RelatorioLivrosApiController extends Controller
{
    private $service;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->service = new RelatorioLivrosService();
    }

    public function index()
    {
        try {
            $this->json($this->service->getReport($_GET));
        } catch (\InvalidArgumentException $exception) {
            $this->json([
                'error' => 'validation_error',
                'message' => $exception->getMessage(),
            ], 400);
        } catch (\Throwable $exception) {
            $this->json([
                'error' => 'internal_error',
                'message' => 'Não foi possivel gerar o relatório.',
            ], 500);
        }
    }
}
