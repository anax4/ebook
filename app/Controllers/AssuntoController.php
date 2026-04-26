<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Assunto;

class AssuntoController extends Controller
{
    private $assunto;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->assunto = new Assunto();
    }

    public function create()
    {
        $this->view('pages/assuntos/form.html.twig', [
            'title' => 'Cadastrar Assunto',
            'action' => '/assuntos',
            'assuntos' => $this->assunto->getAll(),
            'assunto' => null,
        ]);
    }

    public function store()
    {
        $data = [
            'descricao' => trim($_POST['descricao'] ?? ''),
        ];

        $errors = [];

        if ($data['descricao'] === '') {
            $errors[] = 'A descrição do assunto é obrigatória';
        } elseif (strlen($data['descricao']) > 20) {
            $errors[] = 'A descrição do assunto deve ter no máximo 20 caracteres';
        }

        if (!empty($errors)) {
            $this->view('pages/assuntos/form.html.twig', [
                'title' => 'Cadastrar Assunto',
                'action' => '/assuntos',
                'assuntos' => $this->assunto->getAll(),
                'assunto' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $this->assunto->save($data);
        $this->redirect('/assuntos/cadastrar');
    }
}
