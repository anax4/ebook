<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Livro;

class LivroController extends Controller
{
    private $livro;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->livro = new Livro();
    }

    /**
     * Lista todos os livros - GET /
     */
    public function index()
    {
        $livros = $this->livro->getAll();
        
        $this->view('pages/livros/index.html.twig', [
            'livros' => $livros,
            'title' => 'Livros Cadastrados'
        ]);
    }

    /**
     * Formulário de cadastro - GET /livros/cadastrar
     */
    public function create()
    {
        $this->view('pages/livros/form.html.twig', [
            'title' => 'Cadastrar Livro',
            'livro' => null,
            'action' => '/livros'
        ]);
    }

    /**
     * Salva novo livro - POST /livros
     */
    public function store()
    {
        $data = $this->getPostData();
        
        // Validação básica
        $errors = $this->validate($data);
        
        if (!empty($errors)) {
            $this->view('pages/livros/form.html.twig', [
                'title' => 'Cadastrar Livro',
                'livro' => $data,
                'errors' => $errors,
                'action' => '/livros'
            ]);
            return;
        }

        $this->livro->save($data);
        $this->redirect('/');
    }

    /**
     * Formulário de edição - GET /livros/editar/{id}
     */
    public function edit($id)
    {
        $livro = $this->livro->getById($id);
        
        if (!$livro) {
            $this->redirect('/');
        }

        $this->view('pages/livros/form.html.twig', [
            'title' => 'Editar Livro',
            'livro' => $livro,
            'action' => '/livros/atualizar/' . $id
        ]);
    }

    /**
     * Atualiza livro - POST /livros/atualizar/{id}
     */
    public function update($id)
    {
        $data = $this->getPostData();
        $data['id'] = $id;
        
        // Validação básica
        $errors = $this->validate($data);
        
        if (!empty($errors)) {
            $this->view('pages/livros/form.html.twig', [
                'title' => 'Editar Livro',
                'livro' => $data,
                'errors' => $errors,
                'action' => '/livros/atualizar/' . $id
            ]);
            return;
        }

        $this->livro->save($data);
        $this->redirect('/');
    }

    /**
     * Exclui livro - POST /livros/excluir/{id}
     */
    public function destroy($id)
    {
        $this->livro->remove($id);
        $this->redirect('/');
    }

    /**
     * Obtém dados do POST
     */
    private function getPostData()
    {
        return [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'editora' => trim($_POST['editora'] ?? ''),
            'edicao' => (int) ($_POST['edicao'] ?? 0),
            'anoPublicacao' => trim($_POST['anoPublicacao'] ?? '')
        ];
    }

    /**
     * Valida dados do formulário
     */
    private function validate($data)
    {
        $errors = [];
        
        if (empty($data['titulo'])) {
            $errors[] = 'O título é obrigatório';
        } elseif (strlen($data['titulo']) > 40) {
            $errors[] = 'O título deve ter no máximo 40 caracteres';
        }
        
        if (empty($data['editora'])) {
            $errors[] = 'A editora é obrigatória';
        } elseif (strlen($data['editora']) > 40) {
            $errors[] = 'A editora deve ter no máximo 40 caracteres';
        }
        
        if (empty($data['edicao']) || $data['edicao'] < 1) {
            $errors[] = 'A edição é obrigatória e deve ser maior que 0';
        }
        
        if (empty($data['anoPublicacao'])) {
            $errors[] = 'O ano de publicação é obrigatório';
        } elseif (!preg_match('/^\d{4}$/', $data['anoPublicacao'])) {
            $errors[] = 'O ano de publicação deve ter 4 dígitos';
        }
        
        return $errors;
    }
}
