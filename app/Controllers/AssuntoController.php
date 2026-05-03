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
        $this->renderForm('Cadastrar Assunto', '/assuntos');
    }

    public function edit($id)
    {
        $assunto = $this->assunto->getById($id);

        if (!$assunto) {
            $this->redirect('/assuntos/cadastrar');
        }

        $this->renderForm('Editar Assunto', '/assuntos/atualizar/' . $id, $assunto);
    }

    public function store()
    {
        $data = $this->getPostData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->renderForm('Cadastrar Assunto', '/assuntos', $data, $errors);
            return;
        }

        try {
            $this->assunto->save($data);
            $this->redirect('/assuntos/cadastrar');
        } catch (\PDOException $exception) {
            $this->renderForm('Cadastrar Assunto', '/assuntos', $data, [
                'Não foi possível salvar o assunto no banco de dados. Tente novamente.',
            ]);
        }
    }

    public function update($id)
    {
        $data = $this->getPostData();
        $data['id'] = (int) $id;
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->renderForm('Editar Assunto', '/assuntos/atualizar/' . $id, $data, $errors);
            return;
        }

        try {
            $this->assunto->save($data);
            $this->redirect('/assuntos/cadastrar');
        } catch (\PDOException $exception) {
            $this->renderForm('Editar Assunto', '/assuntos/atualizar/' . $id, $data, [
                'Não foi possível atualizar o assunto no banco de dados. Tente novamente.',
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $this->assunto->remove((int) $id);
            $this->redirect('/assuntos/cadastrar');
        } catch (\DomainException $exception) {
            $this->renderForm('Cadastrar Assunto', '/assuntos', null, [$exception->getMessage()]);
        } catch (\PDOException $exception) {
            $this->renderForm('Cadastrar Assunto', '/assuntos', null, [
                'Não foi possível excluir o assunto no banco de dados. Tente novamente.',
            ]);
        }
    }

    private function getPostData(): array
    {
        return [
            'descricao' => trim($_POST['descricao'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['descricao'] === '') {
            $errors[] = 'A descrição do assunto é obrigatória.';
        } elseif (mb_strlen($data['descricao']) > 30) {
            $errors[] = 'A descrição do assunto deve ter no máximo 30 caracteres.';
        }

        return $errors;
    }

    private function renderForm(string $title, string $action, ?array $assunto = null, array $errors = []): void
    {
        $this->view('pages/assuntos/form.html.twig', [
            'title' => $title,
            'action' => $action,
            'assuntos' => $this->assunto->getAll(),
            'assunto' => $assunto,
            'errors' => $errors,
        ]);
    }
}
