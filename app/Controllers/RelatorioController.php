<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\RelatorioLivroAutor;

class RelatorioController extends Controller
{
    private $relatorio;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->relatorio = new RelatorioLivroAutor();
    }

    public function index()
    {
        $this->view('pages/relatorios/index.html.twig', [
            'title' => 'Relatório por autor',
            'grupos' => $this->relatorio->getGroupedByAuthor(),
            'totais' => $this->relatorio->getTotals(),
        ]);
    }
}
