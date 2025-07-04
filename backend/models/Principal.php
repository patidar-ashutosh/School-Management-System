<?php
require_once __DIR__ . '/../config/db.php';

class Principal {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT * FROM principals ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT * FROM principals WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM principals WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }

    public function create($data) {
        $sql = "INSERT INTO principals (password, email, first_name, last_name, phone, address, qualification, joining_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Generate default password if not provided
        if (empty($data['password'])) {
            $data['password'] = strtolower($data['first_name'] . $data['last_name']);
        }
        
        // Hash the password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $this->db->query($sql, [
            $hashedPassword,
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['qualification'] ?? null,
            $data['joining_date'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE principals SET email = ?, first_name = ?, last_name = ?, phone = ?, address = ?, qualification = ?, joining_date = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['qualification'] ?? null,
            $data['joining_date'] ?? null,
            $id
        ]);
    }

    public function updatePassword($id, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE principals SET password = ? WHERE id = ?";
        return $this->db->query($sql, [$hashedPassword, $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM principals WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM principals WHERE email = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $result = $this->db->fetch($sql, [$email, $excludeId]);
        } else {
            $result = $this->db->fetch($sql, [$email]);
        }
        return $result['count'] > 0;
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM principals";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getActivePrincipals() {
        $sql = "SELECT * FROM principals WHERE status = 'active' ORDER BY first_name, last_name";
        return $this->db->fetchAll($sql);
    }

    // Principal-specific methods for managing teachers
    public function getTeachersUnderPrincipal($principalId) {
        $sql = "SELECT t.* FROM teachers t 
                WHERE t.status = 'active' 
                ORDER BY t.first_name, t.last_name";
        return $this->db->fetchAll($sql);
    }

    public function getSchoolStatistics() {
        $stats = [];
        
        // Get total students
        $sql = "SELECT COUNT(*) as count FROM students WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        $stats['total_students'] = $result['count'];
        
        // Get total teachers
        $sql = "SELECT COUNT(*) as count FROM teachers WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        $stats['total_teachers'] = $result['count'];
        
        // Get total classes
        $sql = "SELECT COUNT(*) as count FROM classes WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        $stats['total_classes'] = $result['count'];
        
        // Get total subjects
        $sql = "SELECT COUNT(*) as count FROM subjects WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        $stats['total_subjects'] = $result['count'];
        
        return $stats;
    }
}
?> 