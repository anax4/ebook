<?php

namespace App\Models;

use App\Core\Model;

class Autor extends Model
{
    protected $table = 'autores';

    public function getAll()
    {
        $stmt = $this->db->query('SELECT * FROM autores ORDER BY nome ASC');

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
