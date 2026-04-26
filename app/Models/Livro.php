<?php

namespace App\Models;

use App\Core\Model;

class Livro extends Model
{
    protected $table = 'livros';

    public function getAll()
    {
        return $this->all();
    }

    public function getById($id)
    {
        return $this->find($id);
    }

    public function getAutorIds($livroId): array
    {
        $stmt = $this->db->prepare('SELECT autor_id FROM livro_autor WHERE livro_id = :livro_id ORDER BY autor_id');
        $stmt->execute(['livro_id' => $livroId]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function getAssuntoIds($livroId): array
    {
        $stmt = $this->db->prepare('SELECT assunto_id FROM livro_assunto WHERE livro_id = :livro_id ORDER BY assunto_id');
        $stmt->execute(['livro_id' => $livroId]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function saveWithRelations(array $data, array $autorIds, array $assuntoIds)
    {
        $this->db->beginTransaction();

        try {
            $payload = $data;
            unset($payload['autor_ids'], $payload['assunto_ids']);

            if (isset($payload['id'])) {
                $livroId = (int) $payload['id'];
                $this->update($livroId, $payload);
            } else {
                $livroId = (int) $this->create($payload);
            }

            $this->syncPivot('livro_autor', 'autor_id', $livroId, $autorIds);
            $this->syncPivot('livro_assunto', 'assunto_id', $livroId, $assuntoIds);

            $this->db->commit();

            return $livroId;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function remove($id)
    {
        $this->db->beginTransaction();

        try {
            $this->deletePivotRows('livro_autor', (int) $id);
            $this->deletePivotRows('livro_assunto', (int) $id);
            $this->delete($id);
            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    private function syncPivot(string $table, string $relatedKey, int $livroId, array $relatedIds): void
    {
        $this->deletePivotRows($table, $livroId);

        $stmt = $this->db->prepare(
            sprintf('INSERT INTO %s (livro_id, %s) VALUES (:livro_id, :related_id)', $table, $relatedKey)
        );

        foreach ($relatedIds as $relatedId) {
            $stmt->execute([
                'livro_id' => $livroId,
                'related_id' => $relatedId,
            ]);
        }
    }

    private function deletePivotRows(string $table, int $livroId): void
    {
        $stmt = $this->db->prepare(sprintf('DELETE FROM %s WHERE livro_id = :livro_id', $table));
        $stmt->execute(['livro_id' => $livroId]);
    }
}
