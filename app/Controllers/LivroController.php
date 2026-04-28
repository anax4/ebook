<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Assunto;
use App\Models\Autor;
use App\Models\Livro;

class LivroController extends Controller
{
    private $livro;
    private $autor;
    private $assunto;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->livro = new Livro();
        $this->autor = new Autor();
        $this->assunto = new Assunto();
    }

    public function index()
    {
        $this->view('pages/livros/index.html.twig', [
            'livros' => $this->livro->getAll(),
            'title' => 'Livros Cadastrados',
        ]);
    }

    public function create()
    {
        $this->renderForm('Cadastrar Livro', '/livros');
    }

    public function store()
    {
        $data = $this->getPostData();
        $errors = $this->validate($data);
        if(!empty($errors)){
            $this->renderFormWithErrors('Cadastrar Livro', '/livros', $data, $errors);
            return;
        }

        $this->livro->saveWithRelations($data, $data['autor_ids'], $data['assunto_ids']);
        $this->redirect('/livros/cadastrar');
    }

    public function edit($id)
    {
        $id = (int) $id;
        $livro = $this->livro->getById($id);

        if (!$livro) {
            $this->redirect('/');
        }

        $this->view('pages/livros/form.html.twig', [
            'title' => 'Editar Livro',
            'livro' => $livro,
            'action' => '/livros/atualizar/' . $id,
            'autores' => $this->autor->getAll(),
            'assuntos' => $this->assunto->getAll(),
            'selectedAutores' => $this->livro->getAutorIds($id),
            'selectedAssuntos' => $this->livro->getAssuntoIds($id),
        ]);
    }

    public function update($id)
    {
        $id = (int) $id;
        $data = $this->getPostData();
        $data['id'] = $id;
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->renderFormWithErrors('Editar Livro', '/livros/atualizar/' . $id, $data, $errors);
            return;
        }

        $this->livro->saveWithRelations($data, $data['autor_ids'], $data['assunto_ids']);
        $this->redirect('/');
    }

    public function destroy($id)
    {
        $this->livro->remove((int) $id);
        $this->redirect('/');
    }

    private function getPostData(): array
    {
        return [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'editora' => trim($_POST['editora'] ?? ''),
            'edicao' => (int) ($_POST['edicao'] ?? 0),
            'anoPublicacao' => trim($_POST['anoPublicacao'] ?? ''),
            'valor' => $this->normalizeMoney($_POST['valor'] ?? ''),
            'autor_ids' => $this->normalizeIds($_POST['autor_ids'] ?? []),
            'assunto_ids' => $this->normalizeIds($_POST['assunto_ids'] ?? []),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        $autorIdsDisponiveis = $this->extractIds($this->autor->getAll());
        $assuntoIdsDisponiveis = $this->extractIds($this->assunto->getAll());

        if ($data['titulo'] === '') {
            $errors[] = 'O título é obrigatório';
        } elseif (mb_strlen($data['titulo']) > 40) {
            $errors[] = 'O título deve ter no máximo 40 caracteres';
        }

        if ($data['editora'] === '') {
            $errors[] = 'A editora é obrigatória';
        } elseif (mb_strlen($data['editora']) > 40) {
            $errors[] = 'A editora deve ter no máximo 40 caracteres';
        }

        if ($data['edicao'] < 1) {
            $errors[] = 'A edição é obrigatória e deve ser maior que 0';
        }

        if ($data['anoPublicacao'] === '') {
            $errors[] = 'O ano de publicação é obrigatório';
        } elseif (!preg_match('/^\d{4}$/', $data['anoPublicacao'])) {
            $errors[] = 'O ano de publicação deve ter 4 dígitos';
        }

        if ($data['valor'] === null) {
            $errors[] = 'O valor do livro é obrigatório';
        } elseif ((float) $data['valor'] < 0) {
            $errors[] = 'O valor do livro deve ser maior ou igual a zero';
        }

        if (empty($data['autor_ids'])) {
            $errors[] = 'Selecione pelo menos um autor';
        } elseif (count(array_diff($data['autor_ids'], $autorIdsDisponiveis)) > 0) {
            $errors[] = 'Existe autor selecionado inválido';
        }

        if (empty($data['assunto_ids'])) {
            $errors[] = 'Selecione pelo menos um assunto';
        } elseif (count(array_diff($data['assunto_ids'], $assuntoIdsDisponiveis)) > 0) {
            $errors[] = 'Existe assunto selecionado inválido';
        }

        return $errors;
    }

    private function renderFormWithErrors(string $title, string $action, array $data, array $errors): void
    {
        $this->view('pages/livros/form.html.twig', [
            'title' => $title,
            'livro' => $data,
            'errors' => $errors,
            'action' => $action,
            'autores' => $this->autor->getAll(),
            'assuntos' => $this->assunto->getAll(),
            'selectedAutores' => $data['autor_ids'] ?? [],
            'selectedAssuntos' => $data['assunto_ids'] ?? [],
        ]);
    }

    private function normalizeMoney($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(' ', '', $value);

        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function normalizeIds($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });

        return array_values(array_unique($ids));
    }

    private function extractIds(array $items): array
    {
        return array_map('intval', array_column($items, 'id'));
    }

    private function renderForm(string $title, string $action, ?array $livro = null, array $errors = []): void
    {
        $this->view('pages/livros/form.html.twig', [
            'title' => $title,
            'livro' => $livro,
            'action' => $action,
            'autores' => $this->autor->getAll(),
            'assuntos' => $this->assunto->getAll(),
            'selectedAutores' => [],
            'selectedAssuntos' => [],
            'errors' => $errors,
        ]);
    }
}
