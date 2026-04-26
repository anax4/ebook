<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Autor;

class AutorController extends Controller
{
    private $autor;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->autor = new Autor();
    }

    public function create()
    {
        $this->view('pages/autores/form.html.twig', [
            'title' => 'Cadastrar Autor',
            'action' => '/autores',
            'autores' => $this->autor->getAll(),
            'autor' => null,
        ]);
    }

    public function store()
    {
        $data = [
            'nome' => trim($_POST['nome'] ?? ''),
        ];

        $errors = [];

        if ($data['nome'] === '') {
            $errors[] = 'O nome do autor é obrigatório';
        } elseif (strlen($data['nome']) > 40) {
            $errors[] = 'O nome do autor deve ter no máximo 40 caracteres';
        }

        if (!empty($errors)) {
            $this->view('pages/autores/form.html.twig', [
                'title' => 'Cadastrar Autor',
                'action' => '/autores',
                'autores' => $this->autor->getAll(),
                'autor' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $this->autor->save($data);
        $this->redirect('/autores/cadastrar');
    }
}
