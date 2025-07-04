<?php
require_once __DIR__ . '/../config/db.php';

class Attendance {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    public function create($data) {
        $sql = "INSERT INTO attendance (student_id, class_id, date, status, marked_by) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['student_id'],
            $data['class_id'],
            $data['date'],
            $data['status'],
            $data['marked_by']
        ];
        
        return $this->db->insert($sql, $params);
    }
    
    public function createMultiple($attendanceData) {
        $sql = "INSERT INTO attendance (student_id, class_id, date, status, marked_by) 
                VALUES (?, ?, ?, ?, ?)";
        
        $this->db->beginTransaction();
        
        try {
            foreach ($attendanceData as $data) {
                $params = [
                    $data['student_id'],
                    $data['class_id'],
                    $data['date'],
                    $data['status'],
                    $data['marked_by']
                ];
                $this->db->insert($sql, $params);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function getByClassAndDate($classId, $date) {
        $sql = "SELECT a.*, s.first_name, s.last_name, s.roll_number 
                FROM attendance a 
                JOIN students s ON a.student_id = s.id 
                WHERE a.class_id = ? AND a.date = ? 
                ORDER BY s.first_name, s.last_name";
        
        return $this->db->fetchAll($sql, [$classId, $date]);
    }
    
    public function getStudentsByClass($classId) {
        $sql = "SELECT id, first_name, last_name, roll_number 
                FROM students 
                WHERE class_id = ? AND status = 'active' 
                ORDER BY first_name, last_name";
        
        return $this->db->fetchAll($sql, [$classId]);
    }
    
    public function getTodayAttendance($teacherId) {
        $today = date('Y-m-d');
        
        $sql = "SELECT a.*, s.first_name, s.last_name, c.name as class_name 
                FROM attendance a 
                JOIN students s ON a.student_id = s.id 
                JOIN classes c ON a.class_id = c.id 
                WHERE a.marked_by = ? AND a.date = ?";
        
        return $this->db->fetchAll($sql, [$teacherId, $today]);
    }
    
    public function getAttendanceStats($teacherId) {
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        
        // Today's stats
        $todaySql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                     FROM attendance 
                     WHERE marked_by = ? AND date = ?";
        
        $todayStats = $this->db->fetch($todaySql, [$teacherId, $today]);
        
        // Weekly stats
        $weekSql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                     FROM attendance 
                     WHERE marked_by = ? AND date >= ?";
        
        $weekStats = $this->db->fetch($weekSql, [$teacherId, $weekStart]);
        
        return [
            'today' => [
                'total' => $todayStats['total'] ?? 0,
                'present' => $todayStats['present'] ?? 0,
                'absent' => $todayStats['absent'] ?? 0,
                'late' => $todayStats['late'] ?? 0,
                'percentage' => $todayStats['total'] > 0 ? round(($todayStats['present'] / $todayStats['total']) * 100) : 0
            ],
            'week' => [
                'total' => $weekStats['total'] ?? 0,
                'present' => $weekStats['present'] ?? 0,
                'percentage' => $weekStats['total'] > 0 ? round(($weekStats['present'] / $weekStats['total']) * 100) : 0
            ]
        ];
    }
    
    public function checkExistingAttendance($classId, $date) {
        $sql = "SELECT COUNT(*) as count FROM attendance WHERE class_id = ? AND date = ?";
        $result = $this->db->fetch($sql, [$classId, $date]);
        return $result['count'] > 0;
    }
    
    public function deleteByClassAndDate($classId, $date) {
        $sql = "DELETE FROM attendance WHERE class_id = ? AND date = ?";
        return $this->db->delete($sql, [$classId, $date]);
    }
    
    public function getTotalAttendanceCount($studentId) {
        $sql = "SELECT COUNT(*) as total FROM attendance WHERE student_id = ?";
        $result = $this->db->fetch($sql, [$studentId]);
        return $result ? (int)$result['total'] : 0;
    }

    public function getPresentAttendanceCount($studentId) {
        $sql = "SELECT COUNT(*) as present FROM attendance WHERE student_id = ? AND status = 'present'";
        $result = $this->db->fetch($sql, [$studentId]);
        return $result ? (int)$result['present'] : 0;
    }
}
?> 