<?php
require_once __DIR__ . '/../config/db.php';

class Teacher {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT t.*, s.name as subject_name, c.name as class_teacher_name 
                FROM teachers t 
                LEFT JOIN subjects s ON t.subject_id = s.id 
                LEFT JOIN classes c ON t.class_teacher_of = c.id 
                ORDER BY t.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT * FROM teachers WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM teachers WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }

    public function create($data) {
        $sql = "INSERT INTO teachers (password, email, first_name, last_name, phone, address, subject_id, qualification, experience_years, joining_date, salary, status, class_teacher_of) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
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
            $data['subject_id'] ?? null,
            $data['qualification'] ?? null,
            $data['experience_years'] ?? 0,
            $data['joining_date'] ?? null,
            $data['salary'] ?? null,
            $data['status'] ?? 'active',
            $data['class_teacher_of'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE teachers SET email = ?, first_name = ?, last_name = ?, phone = ?, address = ?, subject_id = ?, qualification = ?, experience_years = ?, joining_date = ?, salary = ?, status = ?, class_teacher_of = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['subject_id'] ?? null,
            $data['qualification'] ?? null,
            $data['experience_years'] ?? 0,
            $data['joining_date'] ?? null,
            $data['salary'] ?? null,
            $data['status'] ?? 'active',
            $data['class_teacher_of'] ?? null,
            $id
        ]);
    }

    public function updatePassword($id, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE teachers SET password = ? WHERE id = ?";
        return $this->db->query($sql, [$hashedPassword, $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM teachers WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM teachers WHERE email = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $result = $this->db->fetch($sql, [$email, $excludeId]);
        } else {
            $result = $this->db->fetch($sql, [$email]);
        }
        return $result['count'] > 0;
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM teachers";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getAvailableTeachers() {
        $sql = "SELECT * FROM teachers WHERE status = 'active'";
        return $this->db->fetchAll($sql);
    }

    public function getBySubject($subjectId) {
        $sql = "SELECT t.* FROM teachers t 
                WHERE t.subject_id = ? AND t.status = 'active'";
        return $this->db->fetchAll($sql, [$subjectId]);
    }

    public function getActiveTeachers() {
        $sql = "SELECT * FROM teachers WHERE status = 'active' ORDER BY first_name, last_name";
        return $this->db->fetchAll($sql);
    }
}
?> 