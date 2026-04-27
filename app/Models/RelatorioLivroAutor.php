<?php

namespace App\Models;

use App\Core\Model;

class RelatorioLivroAutor extends Model
{
    protected $table = 'vw_relatorio_livros_por_autor';

    public function getGroupedByAuthor(): array
    {
        $stmt = $this->db->query(
            'SELECT *
             FROM vw_relatorio_livros_por_autor
             ORDER BY autor_nome ASC, titulo ASC, livro_id ASC'
        );

        $rows = $stmt->fetchAll();
        $grouped = [];

        foreach ($rows as $row) {
            $authorId = (int) $row['autor_id'];

            if (!isset($grouped[$authorId])) {
                $grouped[$authorId] = [
                    'autor_id' => $authorId,
                    'autor_nome' => $row['autor_nome'],
                    'livros' => [],
                ];
            }

            $grouped[$authorId]['livros'][] = [
                'livro_id' => (int) $row['livro_id'],
                'titulo' => $row['titulo'],
                'editora' => $row['editora'],
                'edicao' => (int) $row['edicao'],
                'anoPublicacao' => $row['anoPublicacao'],
                'valor' => (float) $row['valor'],
                'autores_livro' => $row['autores_livro'],
                'assuntos_livro' => $row['assuntos_livro'],
            ];
        }

        return array_values($grouped);
    }

    public function getTotals(): array
    {
        $stmt = $this->db->query(
            'SELECT
                COUNT(DISTINCT autor_id) AS total_autores,
                COUNT(DISTINCT livro_id) AS total_livros
             FROM vw_relatorio_livros_por_autor'
        );

        $totals = $stmt->fetch() ?: [];

        return [
            'autores' => (int) ($totals['total_autores'] ?? 0),
            'livros' => (int) ($totals['total_livros'] ?? 0),
        ];
    }
}
