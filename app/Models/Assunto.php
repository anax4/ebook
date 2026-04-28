<?php

namespace App\Models;

use App\Core\Model;

class Assunto extends Model
{
    protected $table = 'assuntos';

    public function getAll()
    {
        $stmt = $this->db->query(
            'SELECT assuntos.*, COUNT(DISTINCT livro_assunto.livro_id) AS total_livros_relacionados
             FROM assuntos
             LEFT JOIN livro_assunto ON livro_assunto.assunto_id = assuntos.id
             GROUP BY assuntos.id, assuntos.descricao, assuntos.created_at, assuntos.updated_at
             ORDER BY assuntos.descricao ASC'
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
            'SELECT COUNT(DISTINCT livro_id) FROM livro_assunto WHERE assunto_id = :assunto_id'
        );
        $stmt->execute(['assunto_id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    public function hasRelatedBooks(int $id): bool
    {
        return $this->getRelatedBookCount($id) > 0;
    }

    public function remove($id)
    {
        if ($this->hasRelatedBooks((int) $id)) {
            throw new \DomainException('Não é possível excluir este assunto porque existem livros relacionados.');
        }

        return $this->delete($id);
    }
}
