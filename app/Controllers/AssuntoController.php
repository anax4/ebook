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
            $this->flash('error', 'Assunto não encontrado.', 'Registro não localizado');
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

        $this->assunto->save($data);
        $this->flash('success', 'Assunto cadastrado com sucesso.', 'Cadastro concluído');
        $this->redirect('/assuntos/cadastrar');
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

        $this->assunto->save($data);
        $this->flash('success', 'Assunto atualizado com sucesso.', 'Alterações salvas');
        $this->redirect('/assuntos/cadastrar');
    }

    public function delete($id)
    {
        $id = (int) $id;
        $relatedBookCount = $this->assunto->getRelatedBookCount($id);

        if ($relatedBookCount > 0) {
            $this->flash('error', $this->buildDeleteBlockedMessage($relatedBookCount), 'Exclusão não permitida');
            $this->redirect('/assuntos/cadastrar');
        }

        try {
            $this->assunto->remove($id);
            $this->flash('success', 'Assunto excluído com sucesso.', 'Exclusão concluída');
            $this->redirect('/assuntos/cadastrar');
        } catch (\DomainException $exception) {
            $this->flash('error', $exception->getMessage(), 'Exclusão não permitida');
            $this->redirect('/assuntos/cadastrar');
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

    private function buildDeleteBlockedMessage(int $relatedBookCount): string
    {
        $label = $relatedBookCount === 1 ? '1 livro relacionado' : $relatedBookCount . ' livros relacionados';

        return 'Não é possível excluir este assunto porque existem ' . $label . '.';
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
