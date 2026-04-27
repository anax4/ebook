<?php

namespace App\Controllers;

use App\Core\Controller;

class RelatorioController extends Controller
{
    public function index()
    {
        $this->view('pages/relatorios/index.html.twig', [
            'title' => 'Relatório por autor',
        ]);
    }
}
