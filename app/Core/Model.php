<?php

namespace App\Core;

class Model
{
    protected $table;
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC");

        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function create($data)
    {
        $fields = array_keys($data);
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
                VALUES (:" . implode(', :', $fields) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $fields = array_keys($data);
        $set = [];

        foreach ($fields as $field) {
            if ($field === 'id') {
                continue;
            }

            $set[] = "$field = :$field";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);

        return $stmt->rowCount();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount();
    }
}
