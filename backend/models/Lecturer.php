<?php
require_once __DIR__ . '/../config/db.php';

class Lecturer {
    private $db;
    public function __construct() {
        $this->db = db();
    }

    public function getByTeacher($teacher_id) {
        $sql = "SELECT l.*, s.name as subject_name, c.name as class_name FROM lecturers l LEFT JOIN subjects s ON l.subject_id = s.id LEFT JOIN classes c ON l.class_id = c.id WHERE l.teacher_id = ? ORDER BY l.date, l.start_time";
        return $this->db->fetchAll($sql, [$teacher_id]);
    }

    public function add($data) {
        $sql = "INSERT INTO lecturers (subject_id, teacher_id, class_id, date, start_time, end_time, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $this->db->query($sql, [
            $data['subject_id'],
            $data['teacher_id'],
            $data['class_id'],
            $data['date'],
            $data['start_time'],
            $data['end_time'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function edit($id, $data) {
        $sql = "UPDATE lecturers SET subject_id = ?, teacher_id = ?, class_id = ?, date = ?, start_time = ?, end_time = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $this->db->query($sql, [
            $data['subject_id'],
            $data['teacher_id'],
            $data['class_id'],
            $data['date'],
            $data['start_time'],
            $data['end_time'],
            $data['status'],
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM lecturers WHERE id = ?";
        $this->db->query($sql, [$id]);
    }

    public function getById($id) {
        $sql = "SELECT l.*, s.name as subject_name, c.name as class_name FROM lecturers l LEFT JOIN subjects s ON l.subject_id = s.id LEFT JOIN classes c ON l.class_id = c.id WHERE l.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function getAll() {
        $sql = "SELECT l.*, s.name as subject_name, c.name as class_name FROM lecturers l LEFT JOIN subjects s ON l.subject_id = s.id LEFT JOIN classes c ON l.class_id = c.id ORDER BY l.date, l.start_time";
        return $this->db->fetchAll($sql);
    }

    public function getCompletedByDateRange($teacher_id, $start_date, $end_date) {
        $sql = "SELECT l.*, s.name as subject_name, c.name as class_name 
                FROM lecturers l 
                LEFT JOIN subjects s ON l.subject_id = s.id 
                LEFT JOIN classes c ON l.class_id = c.id 
                WHERE l.teacher_id = ? AND l.status = 'completed' 
                AND l.date BETWEEN ? AND ?
                ORDER BY l.date DESC, l.start_time";
        return $this->db->fetchAll($sql, [$teacher_id, $start_date, $end_date]);
    }

    public function existsForSubjectClassDay($subject_id, $class_id, $date, $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM lecturers WHERE subject_id = ? AND class_id = ? AND date = ? AND status != 'completed'";
        $params = [$subject_id, $class_id, $date];
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    public function autoUpdateStatus() {
        $updatedCount = 0;
        
        // Get current date and time
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        // Debug: Log current date and time
        error_log("Auto Update - Current Date: $currentDate, Current Time: $currentTime");
        
        // Get all lectures
        $sql = "SELECT id, date, start_time, end_time, status FROM lecturers WHERE status != 'completed'";
        $lectures = $this->db->fetchAll($sql);
        
        error_log("Auto Update - Found " . count($lectures) . " non-completed lectures");
        
        foreach ($lectures as $lecture) {
            $newStatus = $lecture['status'];
            $lectureId = $lecture['id'];
            $lectureDate = $lecture['date'];
            $startTime = $lecture['start_time'];
            $endTime = $lecture['end_time'];
            $currentStatus = $lecture['status'];
            
            error_log("Processing Lecture ID: $lectureId, Date: $lectureDate, Start: $startTime, End: $endTime, Current Status: $currentStatus");
            
            // Condition 1: Same date, current time between start and end time -> running
            if ($lectureDate === $currentDate) {
                error_log("Lecture is today's date");
                if ($currentTime >= $startTime && $currentTime <= $endTime) {
                    error_log("Current time is between start and end time");
                    if ($currentStatus !== 'running') {
                        $newStatus = 'running';
                        error_log("Changing status from $currentStatus to running");
                    }
                }
                // Condition 3: Same date, current time after end time -> completed
                elseif ($currentTime > $endTime) {
                    error_log("Current time is after end time");
                    if ($currentStatus !== 'completed') {
                        $newStatus = 'completed';
                        error_log("Changing status from $currentStatus to completed");
                    }
                }
            }
            // Condition 2: Lecture date is before today -> completed
            elseif ($lectureDate < $currentDate) {
                error_log("Lecture date is before today");
                if ($currentStatus !== 'completed') {
                    $newStatus = 'completed';
                    error_log("Changing status from $currentStatus to completed");
                }
            }
            
            // Update status if it changed
            if ($newStatus !== $currentStatus) {
                $updateSql = "UPDATE lecturers SET status = ?, updated_at = NOW() WHERE id = ?";
                $this->db->query($updateSql, [$newStatus, $lectureId]);
                $updatedCount++;
                error_log("Updated Lecture ID: $lectureId from $currentStatus to $newStatus");
            } else {
                error_log("No status change needed for Lecture ID: $lectureId");
            }
        }
        
        error_log("Auto Update - Total updated: $updatedCount");
        return $updatedCount;
    }
} 