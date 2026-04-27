<?php

namespace App\Models;

use App\Core\Model;

class Autor extends Model
{
    protected $table = 'autores';

    public function getAll()
    {
        $stmt = $this->db->query(
            'SELECT autores.*, COUNT(DISTINCT livro_autor.livro_id) AS total_livros_relacionados
             FROM autores
             LEFT JOIN livro_autor ON livro_autor.autor_id = autores.id
             GROUP BY autores.id, autores.nome, autores.created_at, autores.updated_at
             ORDER BY autores.nome ASC'
        );

        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        return $this->find($id);
    }

    public function save($data)
    {
        if (isset($data['id'])) {
            return $this->update($data['id'], $data);
        }

        return $this->create($data);
    }

    public function getRelatedBookCount(int $id): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT livro_id) FROM livro_autor WHERE autor_id = :autor_id'
        );
        $stmt->execute(['autor_id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    public function hasRelatedBooks(int $id): bool
    {
        return $this->getRelatedBookCount($id) > 0;
    }

    public function remove($id)
    {
        if ($this->hasRelatedBooks((int) $id)) {
            throw new \DomainException('Nao e possivel excluir este autor porque existem livros relacionados.');
        }

        return $this->delete($id);
    }
}
