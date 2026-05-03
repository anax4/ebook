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
        $this->renderForm('Cadastrar Autor', '/autores', null);
    }

    public function store()
    {
        $data = $this->getPostData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->renderForm('Cadastrar Autor', '/autores', $data, $errors);
            return;
        }

        $this->autor->save($data);
        $this->flash('success', 'Autor cadastrado com sucesso.', 'Cadastro concluído');
        $this->redirect('/autores/cadastrar');
    }

    public function edit($id)
    {
        $autor = $this->autor->getById($id);

        if (!$autor) {
            $this->flash('error', 'Autor não encontrado.', 'Registro não localizado');
            $this->redirect('/autores/cadastrar');
        }

        $this->renderForm('Editar Autor', '/autores/atualizar/' . $id, $autor);
    }

    public function update($id)
    {
        $data = $this->getPostData();
        $data['id'] = (int) $id;
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->renderForm('Editar Autor', '/autores/atualizar/' . $id, $data, $errors);
            return;
        }

        $this->autor->save($data);
        $this->flash('success', 'Autor atualizado com sucesso.', 'Alterações salvas');
        $this->redirect('/autores/cadastrar');
    }

    public function delete($id)
    {
        $id = (int) $id;
        $relatedBookCount = $this->autor->getRelatedBookCount($id);

        if ($relatedBookCount > 0) {
            $this->flash('error', $this->buildDeleteBlockedMessage($relatedBookCount), 'Exclusão não permitida');
            $this->redirect('/autores/cadastrar');
        }

        try {
            $this->autor->remove($id);
            $this->flash('success', 'Autor excluído com sucesso.', 'Exclusão concluída');
            $this->redirect('/autores/cadastrar');
        } catch (\DomainException $exception) {
            $this->flash('error', $exception->getMessage(), 'Exclusão não permitida');
            $this->redirect('/autores/cadastrar');
        }
    }

    private function getPostData(): array
    {
        return [
            'nome' => trim($_POST['nome'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['nome'] === '') {
            $errors[] = 'O nome do autor é obrigatório.';
        } elseif (mb_strlen($data['nome']) > 40) {
            $errors[] = 'O nome do autor deve ter no máximo 40 caracteres.';
        }

        return $errors;
    }

    private function buildDeleteBlockedMessage(int $relatedBookCount): string
    {
        $label = $relatedBookCount === 1 ? '1 livro relacionado' : $relatedBookCount . ' livros relacionados';

        return 'Não é possível excluir este autor porque existem ' . $label . '.';
    }

    private function renderForm(string $title, string $action, ?array $autor = null, array $errors = []): void
    {
        $this->view('pages/autores/form.html.twig', [
            'title' => $title,
            'action' => $action,
            'autores' => $this->autor->getAll(),
            'autor' => $autor,
            'errors' => $errors,
        ]);
    }
}
