<?php
require_once __DIR__ . '/../config/db.php';

class Admin {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function authenticate($email, $password) {
        $sql = "SELECT * FROM admins WHERE email = ?";
        $admin = $this->db->fetch($sql, [$email]);
        
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    }

    public function getById($id) {
        $sql = "SELECT * FROM admins WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function getAll() {
        $sql = "SELECT * FROM admins ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function create($data) {
        $sql = "INSERT INTO admins (email, password, role) VALUES (?, ?, ?)";
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $this->db->query($sql, [
            $data['email'],
            $hashedPassword,
            $data['role']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE admins SET email = ?, role = ? WHERE id = ?";
        return $this->db->query($sql, [
            $data['email'],
            $data['role'],
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM admins WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM admins WHERE email = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $result = $this->db->fetch($sql, [$email, $excludeId]);
        } else {
            $result = $this->db->fetch($sql, [$email]);
        }
        return $result['count'] > 0;
    }
}
?> 