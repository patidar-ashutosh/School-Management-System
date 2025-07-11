<?php
/**
 * Sample Data Insertion Script for School Management System
 * Inserts comprehensive Indian school data into all tables for testing
 * Run this after setup_database.php
 */

$host = '127.0.0.1';
$dbname = 'school_management';
$username = 'root';
$password = '12345';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// 1. Principals
$principalPassword = password_hash('priyasharma', PASSWORD_DEFAULT);
$pdo->prepare("INSERT IGNORE INTO principals (password, email, first_name, last_name, phone, address, qualification, joining_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
    ->execute([$principalPassword, 'principal@school.com', 'Priya', 'Sharma', '9876543200', 'Delhi Public School, Sector 45, Gurgaon', 'M.Ed, PhD', '2015-04-01']);

// 2. Classes
$classes = [
    ['Class 10A', 101, 40],
    ['Class 10B', 102, 38],
    ['Class 9A', 201, 42],
    ['Class 11 Science', 301, 35],
    ['Class 12 Commerce', 401, 30],
];
foreach ($classes as $c) {
    $pdo->prepare("INSERT IGNORE INTO classes (name, room_number, capacity) VALUES (?, ?, ?)")->execute($c);
}

// Fetch class IDs
$classIds = [];
foreach ($classes as $c) {
    $stmt = $pdo->prepare("SELECT id FROM classes WHERE name = ?");
    $stmt->execute([$c[0]]);
    $classIds[$c[0]] = $stmt->fetchColumn();
}

// 3. Subjects
$subjects = [
    ['Mathematics', 'MATH10A', 'Algebra, Geometry, Trigonometry', $classIds['Class 10A']],
    ['Science', 'SCI10A', 'Physics, Chemistry, Biology', $classIds['Class 10A']],
    ['English', 'ENG10A', 'English Literature and Grammar', $classIds['Class 10A']],
    ['Mathematics', 'MATH9A', 'Algebra, Geometry', $classIds['Class 9A']],
    ['Accountancy', 'ACC12C', 'Accountancy for Commerce', $classIds['Class 12 Commerce']],
    ['Business Studies', 'BST12C', 'Business Studies for Commerce', $classIds['Class 12 Commerce']],
    ['Physics', 'PHY11S', 'Physics for Science', $classIds['Class 11 Science']],
    ['Chemistry', 'CHEM11S', 'Chemistry for Science', $classIds['Class 11 Science']],
];
foreach ($subjects as $s) {
    $pdo->prepare("INSERT IGNORE INTO subjects (name, code, description, class_id) VALUES (?, ?, ?, ?)")->execute($s);
}

// Fetch subject IDs
$subjectIds = [];
foreach ($subjects as $s) {
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE code = ?");
    $stmt->execute([$s[1]]);
    $subjectIds[$s[1]] = $stmt->fetchColumn();
}

// 4. Teachers
$teachers = [
    ['teacher1@school.com', 'amitsingh', 'Amit', 'Singh', '9876543212', 'Bangalore', $subjectIds['MATH10A'], 'M.Sc Mathematics', 10, '2014-06-01', 50000, 'active', $classIds['Class 10A']],
    ['teacher2@school.com', 'nehapatel', 'Neha', 'Patel', '9876543213', 'Chennai', $subjectIds['ENG10A'], 'M.A. English', 8, '2016-07-15', 48000, 'active', $classIds['Class 10B']],
    ['teacher3@school.com', 'vikramgupta', 'Vikram', 'Gupta', '9876543214', 'Kolkata', $subjectIds['SCI10A'], 'M.Sc Science', 9, '2015-08-01', 51000, 'active', $classIds['Class 9A']],
    ['teacher4@school.com', 'sunitajain', 'Sunita', 'Jain', '9876543215', 'Mumbai', $subjectIds['ACC12C'], 'M.Com', 12, '2012-05-10', 53000, 'active', $classIds['Class 12 Commerce']],
    ['teacher5@school.com', 'rajivmehra', 'Rajiv', 'Mehra', '9876543216', 'Delhi', $subjectIds['PHY11S'], 'M.Sc Physics', 11, '2013-09-20', 52000, 'active', $classIds['Class 11 Science']],
];
foreach ($teachers as $t) {
    $pdo->prepare("INSERT IGNORE INTO teachers (password, email, first_name, last_name, phone, address, subject_id, qualification, experience_years, joining_date, salary, status, class_teacher_of) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([
            password_hash($t[1], PASSWORD_DEFAULT), $t[0], $t[2], $t[3], $t[4], $t[5], $t[6], $t[7], $t[8], $t[9], $t[10], $t[11], $t[12]
        ]);
}
// Fetch teacher IDs
$teacherIds = [];
foreach ($teachers as $t) {
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE email = ?");
    $stmt->execute([$t[0]]);
    $teacherIds[$t[0]] = $stmt->fetchColumn();
}

// 5. Students
$students = [
    ['student1@school.com', 'arjunreddy', 'Arjun', 'Reddy', '2007-05-10', 'male', 'Hyderabad', '9876543210', 'Ramesh Reddy', '9876543211', 'parent1@school.com', $classIds['Class 10A'], 1001, '2020-06-15', 'B+', 'active'],
    ['student2@school.com', 'zarakhan', 'Zara', 'Khan', '2008-08-22', 'female', 'Mumbai', '9876543215', 'Imran Khan', '9876543216', 'parent2@school.com', $classIds['Class 10B'], 1002, '2021-06-10', 'O+', 'active'],
    ['student3@school.com', 'ishaanverma', 'Ishaan', 'Verma', '2007-12-05', 'male', 'Delhi', '9876543217', 'Suresh Verma', '9876543218', 'parent3@school.com', $classIds['Class 9A'], 1003, '2022-06-12', 'A+', 'active'],
    ['student4@school.com', 'meghasharma', 'Megha', 'Sharma', '2006-03-15', 'female', 'Pune', '9876543219', 'Anil Sharma', '9876543220', 'parent4@school.com', $classIds['Class 11 Science'], 1004, '2019-06-20', 'AB+', 'active'],
    ['student5@school.com', 'rohanjoshi', 'Rohan', 'Joshi', '2005-11-30', 'male', 'Ahmedabad', '9876543221', 'Mahesh Joshi', '9876543222', 'parent5@school.com', $classIds['Class 12 Commerce'], 1005, '2018-06-18', 'B-', 'active'],
];
foreach ($students as $s) {
    $pdo->prepare("INSERT IGNORE INTO students (password, email, first_name, last_name, date_of_birth, gender, address, phone, parent_name, parent_phone, parent_email, class_id, roll_number, admission_date, blood_group, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([
            password_hash($s[1], PASSWORD_DEFAULT), $s[0], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10], $s[11], $s[12], $s[13], $s[14], $s[15]
        ]);
}
// Fetch student IDs
$studentIds = [];
foreach ($students as $s) {
    $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->execute([$s[0]]);
    $studentIds[$s[0]] = $stmt->fetchColumn();
}

// 6. Attendance (varied: present/absent, different days, different teachers)
$attendance = [
    [$studentIds['student1@school.com'], $classIds['Class 10A'], '2024-07-01', 'present', $teacherIds['teacher1@school.com']],
    [$studentIds['student2@school.com'], $classIds['Class 10B'], '2024-07-01', 'absent', $teacherIds['teacher2@school.com']],
    [$studentIds['student3@school.com'], $classIds['Class 9A'], '2024-07-01', 'present', $teacherIds['teacher3@school.com']],
    [$studentIds['student4@school.com'], $classIds['Class 11 Science'], '2024-07-01', 'present', $teacherIds['teacher5@school.com']],
    [$studentIds['student5@school.com'], $classIds['Class 12 Commerce'], '2024-07-01', 'absent', $teacherIds['teacher4@school.com']],
];
foreach ($attendance as $a) {
    $pdo->prepare("INSERT IGNORE INTO attendance (student_id, class_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?)")->execute($a);
}

// 7. Assignments (varied types, statuses, teachers, classes)
$assignments = [
    ['Algebra Test', 'Algebraic expressions', $subjectIds['MATH10A'], $classIds['Class 10A'], '2024-06-20', '2024-06-27', 100, $teacherIds['teacher1@school.com'], 'essays', 'completed'],
    ['English Essay', 'Essay on Indian Independence', $subjectIds['ENG10A'], $classIds['Class 10B'], '2024-06-22', '2024-06-29', 50, $teacherIds['teacher2@school.com'], 'reports', 'running'],
    ['Physics Lab', 'Experiment on Light', $subjectIds['PHY11S'], $classIds['Class 11 Science'], '2024-06-25', '2024-07-02', 75, $teacherIds['teacher5@school.com'], 'presentations', 'coming'],
];
foreach ($assignments as $a) {
    $pdo->prepare("INSERT IGNORE INTO assignments (title, description, subject_id, class_id, start_date, due_date, total_marks, teacher_id, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($a);
}
// Fetch assignment IDs
$assignmentIds = [];
foreach ($assignments as $a) {
    $stmt = $pdo->prepare("SELECT id FROM assignments WHERE title = ?");
    $stmt->execute([$a[0]]);
    $assignmentIds[$a[0]] = $stmt->fetchColumn();
}

// 8. Student Assignments (submissions, graded/ungraded)
$studentAssignments = [
    [$assignmentIds['Algebra Test'], $studentIds['student1@school.com'], '2024-06-26 10:00:00', 95, 'Well done', null, 'graded'],
    [$assignmentIds['English Essay'], $studentIds['student2@school.com'], '2024-06-28 12:00:00', 88, 'Good analysis', null, 'graded'],
    [$assignmentIds['Physics Lab'], $studentIds['student4@school.com'], null, null, null, null, 'submitted'],
];
foreach ($studentAssignments as $sa) {
    $pdo->prepare("INSERT IGNORE INTO student_assignments (assignment_id, student_id, submitted_date, marks_obtained, submitted_text, submitted_file, status) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute($sa);
}

// 9. Exams (varied types, classes, subjects)
$exams = [
    ['Maths Midterm', $subjectIds['MATH10A'], $classIds['Class 10A'], '2024-07-10', '09:00:00', '11:00:00', 100, 'midterm', 'scheduled'],
    ['English Final', $subjectIds['ENG10A'], $classIds['Class 10B'], '2024-07-15', '10:00:00', '12:00:00', 100, 'final', 'ongoing'],
    ['Physics Final', $subjectIds['PHY11S'], $classIds['Class 11 Science'], '2024-07-20', '13:00:00', '15:00:00', 100, 'final', 'completed'],
];
foreach ($exams as $e) {
    $pdo->prepare("INSERT IGNORE INTO exams (name, subject_id, class_id, date, start_time, end_time, total_marks, exam_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($e);
}

// 10. Lecturers (weekly schedule, different days, times, teachers, classes)
$lecturers = [
    [$subjectIds['MATH10A'], $teacherIds['teacher1@school.com'], $classIds['Class 10A'], 'Monday', '08:00:00', '09:00:00', 'incoming'],
    [$subjectIds['ENG10A'], $teacherIds['teacher2@school.com'], $classIds['Class 10B'], 'Tuesday', '09:00:00', '10:00:00', 'incoming'],
    [$subjectIds['PHY11S'], $teacherIds['teacher5@school.com'], $classIds['Class 11 Science'], 'Wednesday', '10:00:00', '11:00:00', 'completed'],
];
foreach ($lecturers as $l) {
    $pdo->prepare("INSERT IGNORE INTO lecturers (subject_id, teacher_id, class_id, day_of_week, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute($l);
}

// 11. Teacher Classes (many-to-many)
$teacherClasses = [
    [$teacherIds['teacher1@school.com'], $classIds['Class 10A']],
    [$teacherIds['teacher1@school.com'], $classIds['Class 10B']],
    [$teacherIds['teacher2@school.com'], $classIds['Class 10B']],
    [$teacherIds['teacher3@school.com'], $classIds['Class 9A']],
    [$teacherIds['teacher4@school.com'], $classIds['Class 12 Commerce']],
    [$teacherIds['teacher5@school.com'], $classIds['Class 11 Science']],
];
foreach ($teacherClasses as $tc) {
    $pdo->prepare("INSERT IGNORE INTO teacher_classes (teacher_id, class_id) VALUES (?, ?)")->execute($tc);
}

// 12. Password Resets (for all user types)
// $passwordResets = [
//     ['principal@school.com', 'principal', bin2hex(random_bytes(32)), date('Y-m-d H:i:s', strtotime('+1 day')), false],
//     ['teacher1@school.com', 'teacher', bin2hex(random_bytes(32)), date('Y-m-d H:i:s', strtotime('+1 day')), false],
//     ['student1@school.com', 'student', bin2hex(random_bytes(32)), date('Y-m-d H:i:s', strtotime('+1 day')), false],
// ];
// foreach ($passwordResets as $pr) {
//     $pdo->prepare("INSERT IGNORE INTO password_resets (email, user_type, token, expires_at, used) VALUES (?, ?, ?, ?, ?)")->execute($pr);
// }

echo "Sample data inserted successfully!\n";
echo "\nDefault login credentials:\n";
echo "Principal: principal@school.com / priyasharma\n";
echo "Teachers: teacher1@school.com / amitsingh, teacher2@school.com / nehapatel, teacher3@school.com / vikramgupta, teacher4@school.com / sunitajain, teacher5@school.com / rajivmehra\n";
echo "Students: student1@school.com / arjunreddy, student2@school.com / zarakhan, student3@school.com / ishaanverma, student4@school.com / meghasharma, student5@school.com / rohanjoshi\n\n";
echo "Sample Data Features:\n";
echo "- Indian names: Arjun Reddy, Zara Khan, Ishaan Verma, Megha Sharma, Rohan Joshi\n";
echo "- Teachers: Amit Singh, Neha Patel, Vikram Gupta, Sunita Jain, Rajiv Mehra\n";
echo "- Principal: Priya Sharma\n";
echo "- Subjects: Mathematics, Science, English, Accountancy, Business Studies, Physics, Chemistry\n";
echo "- Classes: Class 9A, 10A, 10B, 11 Science, 12 Commerce\n";
echo "- Complete attendance, grades, exams, assignments, and schedule data\n";
echo "- Students use email for login, roll_number is unique\n\n"; 