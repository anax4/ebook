<?php

namespace App\Models;

use App\Core\Model;

class Assunto extends Model
{
    protected $table = 'assuntos';

    public function getAll()
    {
        $stmt = $this->db->query('SELECT * FROM assuntos ORDER BY descricao ASC');

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

    public function remove($id)
    {
        return $this->delete($id);
    }
}
