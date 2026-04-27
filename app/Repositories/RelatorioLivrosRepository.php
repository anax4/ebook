<?php

namespace App\Repositories;

use App\Core\Database;

class RelatorioLivrosRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                autor_id,
                autor_nome,
                livro_id,
                titulo,
                editora,
                anoPublicacao,
                valor,
                assuntos_livro,
                autores_livro
             FROM vw_relatorio_livros_por_autor'
        );

        return $stmt->fetchAll();
    }
}
