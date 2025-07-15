<?php
date_default_timezone_set('UTC');
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

// Helper: get all subjects for a class
function getSubjectsForClass($classId, $subjectClassMap, $subjectIds, $classIds) {
    $subjects = [];
    foreach ($subjectClassMap as $subjectCode => $classNames) {
        foreach ($classNames as $className) {
            if ($classIds[$className] == $classId) {
                $subjects[] = $subjectIds[$subjectCode];
            }
        }
    }
    return $subjects;
}

// 1. Principals
$principalPassword = password_hash('priyasharma', PASSWORD_DEFAULT);
$pdo->prepare("INSERT IGNORE INTO principals (password, email, first_name, last_name, phone, address, qualification, joining_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
    ->execute([$principalPassword, 'principal@school.com', 'Priya', 'Sharma', '9876543200', 'Delhi Public School, Sector 45, Gurgaon', 'M.Ed, PhD', '2015-04-01']);

// 2. Classes (10 classes: Class 1 to Class 10)
$classes = [
    ['Class 1', 101, 40],
    ['Class 2', 102, 38],
    ['Class 3', 103, 42],
    ['Class 4', 104, 39],
    ['Class 5', 105, 41],
    ['Class 6', 106, 40],
    ['Class 7', 107, 38],
    ['Class 8', 108, 40],
    ['Class 9', 109, 37],
    ['Class 10', 110, 39],
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

// 3. Subjects (7 subjects)
$subjects = [
    ['Mathematics', 'MATH', 'Mathematics for all classes'],
    ['English', 'ENG', 'English for all classes'],
    ['Science', 'SCI', 'Science for all classes'],
    ['Hindi', 'HIN', 'Hindi for all classes'],
    ['Social Studies', 'SST', 'Social Studies for all classes'],
    ['Computer Science', 'CS', 'Computer Science for all classes'],
    ['Business Education', 'BE', 'Business Education for all classes'], // replaced Physical Education
];
foreach ($subjects as $s) {
    $pdo->prepare("INSERT IGNORE INTO subjects (name, code, description) VALUES (?, ?, ?)")->execute($s);
}

// Fetch subject IDs
$subjectIds = [];
foreach ($subjects as $s) {
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE code = ?");
    $stmt->execute([$s[1]]);
    $subjectIds[$s[1]] = $stmt->fetchColumn();
}

// Custom subject-class mapping
$classNameToId = array_flip($classIds); // for reverse lookup
$subjectClassMap = [
    'MATH' => ['Class 1', 'Class 2', 'Class 3', 'Class 4'],
    'ENG' => ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 8', 'Class 9', 'Class 10'],
    'HIN' => ['Class 1', 'Class 2', 'Class 3', 'Class 4'],
    'SST' => ['Class 5', 'Class 6', 'Class 7', 'Class 8'],
    'SCI' => ['Class 8', 'Class 9', 'Class 10'],
    'CS'  => ['Class 8', 'Class 9', 'Class 10'],
    'BE'  => ['Class 8', 'Class 9', 'Class 10'],
];
foreach ($subjectClassMap as $subjectCode => $classNames) {
    $subjectId = $subjectIds[$subjectCode];
    foreach ($classNames as $className) {
        $classId = $classIds[$className];
        $pdo->prepare("INSERT IGNORE INTO subject_classes (subject_id, class_id) VALUES (?, ?)")->execute([$subjectId, $classId]);
    }
}

// 4. Teachers (10 teachers with specific class assignments)
$teachers = [
    // email, password, first_name, last_name, phone, address, subject_id, qualification, experience_years, joining_date, salary, status, class_teacher_of
    ['teacher1@school.com', 'amitsingh', 'Amit', 'Singh', '9876543212', 'Bangalore', $subjectIds['MATH'], 'B.Sc Mathematics', 5, '2018-06-01', 40000, 'active', $classIds['Class 1']],
    ['teacher2@school.com', 'nehapatel', 'Neha', 'Patel', '9876543213', 'Chennai', $subjectIds['ENG'], 'B.A. English', 6, '2017-07-15', 40000, 'active', $classIds['Class 2']],
    ['teacher3@school.com', 'vikramgupta', 'Vikram', 'Gupta', '9876543214', 'Kolkata', $subjectIds['SCI'], 'B.Sc Science', 7, '2016-08-01', 40000, 'active', $classIds['Class 3']],
    ['teacher4@school.com', 'sunitajain', 'Sunita', 'Jain', '9876543215', 'Mumbai', $subjectIds['HIN'], 'B.A. Hindi', 8, '2015-05-10', 40000, 'active', $classIds['Class 4']],
    ['teacher5@school.com', 'rajivmehra', 'Rajiv', 'Mehra', '9876543216', 'Delhi', $subjectIds['SST'], 'B.A. Social', 9, '2014-09-20', 40000, 'active', $classIds['Class 5']],
    ['teacher6@school.com', 'priyagupta', 'Priya', 'Gupta', '9876543217', 'Pune', $subjectIds['CS'], 'B.Tech Computer Science', 10, '2013-06-01', 40000, 'active', $classIds['Class 6']],
    ['teacher7@school.com', 'manishkumar', 'Manish', 'Kumar', '9876543218', 'Lucknow', $subjectIds['BE'], 'B.P.Ed', 11, '2012-07-15', 40000, 'active', $classIds['Class 7']],
    ['teacher8@school.com', 'deepashah', 'Deepa', 'Shah', '9876543219', 'Ahmedabad', $subjectIds['MATH'], 'M.Sc Mathematics', 12, '2011-08-01', 40000, 'active', $classIds['Class 8']],
    ['teacher9@school.com', 'sureshchandra', 'Suresh', 'Chandra', '9876543220', 'Hyderabad', $subjectIds['ENG'], 'M.A. English', 13, '2010-05-10', 40000, 'active', $classIds['Class 9']],
    ['teacher10@school.com', 'anitaroy', 'Anita', 'Roy', '9876543221', 'Jaipur', $subjectIds['SCI'], 'M.Sc Physics', 14, '2009-09-20', 40000, 'active', $classIds['Class 10']],
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

// 5. Students (5 students per class + 10 extra for Class 1, admission dates: today to minus 5 days)
$students = [];
$firstNames = ['Arjun', 'Zara', 'Ishaan', 'Megha', 'Rohan', 'Simran', 'Kabir', 'Aanya', 'Dev', 'Tara', 'Yash', 'Riya', 'Aarav', 'Anaya', 'Vivaan', 'Diya', 'Aditya', 'Saanvi', 'Krishna', 'Myra', 'Dhruv', 'Kiara', 'Aryan', 'Navya', 'Parth', 'Rahul', 'Priya', 'Amit', 'Neha', 'Vikram', 'Sunita', 'Rajiv', 'Deepa', 'Suresh', 'Anita', 'Alok', 'Meena', 'Prakash', 'Kavita', 'Sanjay', 'Rekha', 'Mohan', 'Geeta', 'Ramesh', 'Lakshmi', 'Suresh', 'Radha', 'Gopal', 'Sita', 'Ram', 'Krishna', 'Radha', 'Balram', 'Subhadra', 'Jagannath', 'Lakshmi', 'Saraswati', 'Ganesh', 'Durga', 'Kali', 'Hanuman', 'Vishnu', 'Shiva', 'Brahma', 'Indra', 'Agni', 'Varuna', 'Vayu', 'Surya', 'Chandra', 'Mangal', 'Budh', 'Guru', 'Shukra', 'Shani', 'Rahu', 'Ketu'];
$lastNames = ['Reddy', 'Khan', 'Verma', 'Sharma', 'Joshi', 'Singh', 'Patel', 'Gupta', 'Jain', 'Mehra', 'Roy', 'Chandra', 'Kumar', 'Shah', 'Chopra', 'Bansal', 'Kapoor', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu'];

$studentCounter = 1;

// Generate 5 students for each class (Class 1 to Class 10)
foreach ($classes as $classIndex => $class) {
    $className = $class[0];
    $classId = $classIds[$className];
    
    for ($i = 1; $i <= 5; $i++) {
        $first = $firstNames[($studentCounter-1) % count($firstNames)];
        $last = $lastNames[($studentCounter-1) % count($lastNames)];
        $email = strtolower($first.$last."$studentCounter@school.com");
        $username = strtolower($first.$last);
        $gender = ($studentCounter%2==0) ? 'female' : 'male';
        $dob = date('Y-m-d', strtotime("2005-01-01 +".($studentCounter*120).' days'));
        
        // Admission date: today to minus 5 days
        $admissionDaysAgo = ($studentCounter-1) % 6; // 0 to 5 days ago
        $admission = date('Y-m-d', strtotime("-$admissionDaysAgo days"));
        
        $students[] = [
            $email,
            $username,
            $first,
            $last,
            $dob,
            $gender,
            'City '.$studentCounter,
            '9876543'.str_pad($studentCounter,3,'0',STR_PAD_LEFT),
            'Parent '.$first,
            '9876543'.str_pad($studentCounter+100,3,'0',STR_PAD_LEFT),
            'parent'.$studentCounter.'@school.com',
            $classId,
            1000 + $studentCounter,
            $admission,
            'A+',
            'active',
        ];
        $studentCounter++;
    }
}

// Add 10 extra students for Class 1
$class1Id = $classIds['Class 1'];
for ($i = 1; $i <= 10; $i++) {
    $first = $firstNames[($studentCounter-1) % count($firstNames)];
    $last = $lastNames[($studentCounter-1) % count($lastNames)];
    $email = strtolower($first.$last."$studentCounter@school.com");
    $username = strtolower($first.$last);
    $gender = ($studentCounter%2==0) ? 'female' : 'male';
    $dob = date('Y-m-d', strtotime("2005-01-01 +".($studentCounter*120).' days'));
    
    // Admission date: today to minus 5 days
    $admissionDaysAgo = ($studentCounter-1) % 6; // 0 to 5 days ago
    $admission = date('Y-m-d', strtotime("-$admissionDaysAgo days"));
    
    $students[] = [
        $email,
        $username,
        $first,
        $last,
        $dob,
        $gender,
        'City '.$studentCounter,
        '9876543'.str_pad($studentCounter,3,'0',STR_PAD_LEFT),
        'Parent '.$first,
        '9876543'.str_pad($studentCounter+100,3,'0',STR_PAD_LEFT),
        'parent'.$studentCounter.'@school.com',
        $class1Id, // All extra students go to Class 1
        1000 + $studentCounter,
        $admission,
        'A+',
        'active',
    ];
    $studentCounter++;
}

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

// 6. Attendance (sample attendance for all students)
$attendance = [];
$currentDate = date('Y-m-d');

// Generate attendance for past 5 days for all students
for ($dayOffset = 5; $dayOffset >= 1; $dayOffset--) {
    $attendanceDate = date('Y-m-d', strtotime("-$dayOffset days"));
    
    foreach ($students as $student) {
        $studentEmail = $student[0];
        if (isset($studentIds[$studentEmail])) {
            $studentId = $studentIds[$studentEmail];
            $classId = $student[11]; // class_id
            
            // Find the class teacher for this class
            $classTeacherId = null;
            foreach ($teachers as $teacher) {
                if ($teacher[12] == $classId) { // class_teacher_of
                    $teacherEmail = $teacher[0];
                    $classTeacherId = $teacherIds[$teacherEmail];
                    break;
                }
            }
            
            if ($classTeacherId) {
                // Randomly assign present/absent (80% present, 20% absent)
                $status = (rand(1, 100) <= 80) ? 'present' : 'absent';
                
                $attendance[] = [
                    $studentId,
                    $classId,
                    $attendanceDate,
                    $status,
                    $classTeacherId
                ];
            }
        }
    }
}

foreach ($attendance as $a) {
    $pdo->prepare("INSERT IGNORE INTO attendance (student_id, class_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?)")->execute($a);
}

// 7. Assignments (sample assignments for all classes)
$assignments = [];
$assignmentTitles = [
    'Mathematics Assignment', 'English Essay', 'Science Project', 'Hindi Literature', 
    'Social Studies Report', 'Computer Science Lab', 'Business Education Test'
];

foreach ($classes as $classIndex => $class) {
    $className = $class[0];
    $classId = $classIds[$className];
    // Find the class teacher for this class
    $classTeacherId = null;
    $classTeacherSubjectId = null;
    foreach ($teachers as $teacher) {
        if ($teacher[12] == $classId) { // class_teacher_of
            $teacherEmail = $teacher[0];
            $classTeacherId = $teacherIds[$teacherEmail];
            $classTeacherSubjectId = $teacher[6]; // subject_id
            break;
        }
    }
    // Get valid subjects for this class
    $validSubjectIds = getSubjectsForClass($classId, $subjectClassMap, $subjectIds, $classIds);
    if ($classTeacherId && $classTeacherSubjectId) {
        // Only create assignments for the subject(s) the class teacher teaches AND that are mapped to the class
        foreach ($subjects as $subjectIndex => $subject) {
            $subjectId = $subjectIds[$subject[1]]; // subject code
            if ($subjectId != $classTeacherSubjectId) continue; // Only for teacher's subject
            if (!in_array($subjectId, $validSubjectIds)) continue; // Only if subject is mapped to class
            $title = $assignmentTitles[$subjectIndex] . ' - ' . $className;
            $description = $subject[0] . ' assignment for ' . $className;
            $startDate = date('Y-m-d', strtotime('-2 days'));
            $dueDate = date('Y-m-d', strtotime('+5 days'));
            $totalMarks = 50 + ($subjectIndex * 10);
            $type = ($subjectIndex % 2 == 0) ? 'essays' : 'reports';
            $status = ($subjectIndex % 3 == 0) ? 'completed' : (($subjectIndex % 3 == 1) ? 'running' : 'coming');
            $assignments[] = [
                $title,
                $description,
                $subjectId,
                $classId,
                $startDate,
                $dueDate,
                $totalMarks,
                $classTeacherId,
                $type,
                $status
            ];
        }
    }
}

foreach ($assignments as $a) {
    $pdo->prepare("INSERT IGNORE INTO assignments (title, description, subject_id, class_id, start_date, due_date, total_marks, teacher_id, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($a);
}

// 8. Student Assignments (sample submissions)
$studentAssignments = [];
foreach ($assignments as $assignmentIndex => $assignment) {
    // Get students for this class
    $classId = $assignment[3]; // class_id from assignment
    $classStudents = array_filter($students, function($student) use ($classId) {
        return $student[11] == $classId;
    });
    // Take first 3 students from each class for submissions
    $count = 0;
    foreach ($classStudents as $student) {
        if ($count >= 3) break;
        $studentEmail = $student[0];
        if (isset($studentIds[$studentEmail])) {
            $studentId = $studentIds[$studentEmail];
            $submittedDate = date('Y-m-d H:i:s', strtotime('-1 day'));
            $status = 'graded';
            $marksObtained = 0;
            if ($status === 'graded') {
                $assignmentTotalMarks = $assignment[6];
                $marksObtained = rand(0, $assignmentTotalMarks); // always <= total_marks
            }
            $feedback = 'Good work on ' . $assignment[0];
            // Add sample text for essays/reports
            $submittedText = null;
            if ($assignment[8] === 'essays') {
                $submittedText = 'Sample essay submission for ' . $assignment[0];
            } elseif ($assignment[8] === 'reports') {
                $submittedText = 'Sample report submission for ' . $assignment[0];
            }
            $studentAssignments[] = [
                $assignmentIndex + 1, // assignment_id (assuming sequential insertion)
                $studentId,
                $submittedDate,
                $marksObtained,
                $submittedText, // submitted_text
                null, // No file submission
                $status
            ];
        }
        $count++;
    }
}

foreach ($studentAssignments as $sa) {
    $pdo->prepare("INSERT IGNORE INTO student_assignments (assignment_id, student_id, submitted_date, marks_obtained, submitted_text, submitted_file, status) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute($sa);
}

// 9. Exams (1 exam per class for today + 5 extra exams for Class 1)
$exams = [];
$currentTime = date('H:i:s');
$startTime = date('H:i:s', strtotime('+5 minutes'));
$endTime = date('H:i:s', strtotime('+30 minutes'));
$today = date('Y-m-d');

// 1 exam per class for today
foreach ($classes as $classIndex => $class) {
    $className = $class[0];
    $classId = $classIds[$className];
    $subjectId = $subjectIds['MATH']; // Default to Mathematics
    $examType = ($classIndex % 2 == 0) ? 'midterm' : 'final';
    $totalMarks = 50 + ($classIndex * 5);
    
    $exams[] = [
        'Mathematics ' . ucfirst($examType) . ' - ' . $className,
        $subjectId,
        $classId,
        $today,
        $startTime,
        $endTime,
        $totalMarks,
        $examType,
        'ongoing'
    ];
}

// 5 extra exams for Class 1 (from tomorrow onwards)
$class1Id = $classIds['Class 1'];
$class1SubjectIds = [$subjectIds['MATH'], $subjectIds['ENG'], $subjectIds['SCI'], $subjectIds['HIN'], $subjectIds['SST']];
$class1ExamTypes = ['midterm', 'final', 'midterm', 'final', 'midterm'];

for ($i = 1; $i <= 5; $i++) {
    $examDate = date('Y-m-d', strtotime("+$i days"));
    $subjectId = $class1SubjectIds[($i-1) % count($class1SubjectIds)];
    $examType = $class1ExamTypes[($i-1) % count($class1ExamTypes)];
    $subjectName = '';
    
    // Get subject name
    foreach ($subjects as $subject) {
        if ($subjectIds[$subject[1]] == $subjectId) {
            $subjectName = $subject[0];
            break;
        }
    }
    
    $exams[] = [
        $subjectName . ' ' . ucfirst($examType) . ' - Class 1',
        $subjectId,
        $class1Id,
        $examDate,
        $startTime,
        $endTime,
        50 + ($i * 5),
        $examType,
        'scheduled'
    ];
}

foreach ($exams as $e) {
    $pdo->prepare("INSERT IGNORE INTO exams (name, subject_id, class_id, date, start_time, end_time, total_marks, exam_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($e);
}

// 10. Teacher Classes (many-to-many relationships)
$teacherClasses = [];

// Teacher 1 teaches Class 1, 2, 3
$teacherClasses[] = [$teacherIds['teacher1@school.com'], $classIds['Class 1']];
$teacherClasses[] = [$teacherIds['teacher1@school.com'], $classIds['Class 2']];
$teacherClasses[] = [$teacherIds['teacher1@school.com'], $classIds['Class 3']];

// Teacher 2 teaches Class 1, 2, 4
$teacherClasses[] = [$teacherIds['teacher2@school.com'], $classIds['Class 1']];
$teacherClasses[] = [$teacherIds['teacher2@school.com'], $classIds['Class 2']];
$teacherClasses[] = [$teacherIds['teacher2@school.com'], $classIds['Class 4']];

// Teacher 3 teaches Class 1, 3, 5
$teacherClasses[] = [$teacherIds['teacher3@school.com'], $classIds['Class 1']];
$teacherClasses[] = [$teacherIds['teacher3@school.com'], $classIds['Class 3']];
$teacherClasses[] = [$teacherIds['teacher3@school.com'], $classIds['Class 5']];

// Teacher 4 teaches Class 4, 6
$teacherClasses[] = [$teacherIds['teacher4@school.com'], $classIds['Class 4']];
$teacherClasses[] = [$teacherIds['teacher4@school.com'], $classIds['Class 6']];

// Teacher 5 teaches Class 5, 7
$teacherClasses[] = [$teacherIds['teacher5@school.com'], $classIds['Class 5']];
$teacherClasses[] = [$teacherIds['teacher5@school.com'], $classIds['Class 7']];

// Teacher 6 teaches Class 6, 8
$teacherClasses[] = [$teacherIds['teacher6@school.com'], $classIds['Class 6']];
$teacherClasses[] = [$teacherIds['teacher6@school.com'], $classIds['Class 8']];

// Teacher 7 teaches Class 7, 9
$teacherClasses[] = [$teacherIds['teacher7@school.com'], $classIds['Class 7']];
$teacherClasses[] = [$teacherIds['teacher7@school.com'], $classIds['Class 9']];

// Teacher 8 teaches Class 8, 10
$teacherClasses[] = [$teacherIds['teacher8@school.com'], $classIds['Class 8']];
$teacherClasses[] = [$teacherIds['teacher8@school.com'], $classIds['Class 10']];

// Teacher 9 teaches Class 9
$teacherClasses[] = [$teacherIds['teacher9@school.com'], $classIds['Class 9']];

// Teacher 10 teaches Class 10
$teacherClasses[] = [$teacherIds['teacher10@school.com'], $classIds['Class 10']];

// Add class_teacher_of assignments to teacher_classes table
foreach ($teachers as $t) {
    $teacherEmail = $t[0];
    $classTeacherOf = $t[12]; // class_teacher_of value
    if ($classTeacherOf) {
        // Add this teacher-class combination to teacher_classes table
        $teacherClasses[] = [$teacherIds[$teacherEmail], $classTeacherOf];
    }
}

foreach ($teacherClasses as $tc) {
    $pdo->prepare("INSERT IGNORE INTO teacher_classes (teacher_id, class_id) VALUES (?, ?)")->execute($tc);
}

// --- Add lectures for every teacher who teaches in Class 1 for this week (Mon-Sat, 7:00-17:00) ---
$class1Id = $classIds['Class 1'];

// Get all teacher IDs who teach in Class 1 (from teacher_classes mapping)
$teachersForClass1 = [];
foreach ($teacherClasses as $tc) {
    if ($tc[1] == $class1Id) {
        $teachersForClass1[] = $tc[0];
    }
}
$teachersForClass1 = array_unique($teachersForClass1);

// Get all subject_ids mapped to Class 1
$subjectsForClass1 = getSubjectsForClass($class1Id, $subjectClassMap, $subjectIds, $classIds);

// Get this week's Monday
$today = new DateTime('now', new DateTimeZone('UTC'));
$dayOfWeek = (int)$today->format('w'); // 0=Sunday, 1=Monday, ...
$daysSinceMonday = ($dayOfWeek + 6) % 7;
$thisMonday = (clone $today)->modify("-{$daysSinceMonday} days");

// For each teacher, add a lecture for each day (Mon-Sat)
foreach ($teachersForClass1 as $idx => $teacherId) {
    // Find the subject this teacher teaches in Class 1
    $teacherSubjectId = null;
    foreach ($teachers as $t) {
        if (isset($teacherIds[$t[0]]) && $teacherIds[$t[0]] == $teacherId) {
            $teacherSubjectId = $t[6]; // subject_id
            break;
        }
    }
    // Only add if this subject is mapped to Class 1
    if (!$teacherSubjectId || !in_array($teacherSubjectId, $subjectsForClass1)) continue;

    // Assign a time slot for this teacher (e.g., 07:00–08:00, 08:00–09:00, ..., up to 16:00–17:00)
    $baseHour = 7 + ($idx % 10); // 7:00, 8:00, ..., up to 16:00
    $startTime = sprintf('%02d:00:00', $baseHour);
    $endTime = sprintf('%02d:00:00', $baseHour + 1);

    for ($i = 0; $i < 6; $i++) { // Monday to Saturday
        $date = (clone $thisMonday)->modify("+{$i} days")->format('Y-m-d');
        // Skip Sunday (shouldn't happen in 0-5 loop, but for safety)
        if ((new DateTime($date))->format('w') == 0) continue;

        $pdo->prepare("INSERT IGNORE INTO lecturers (subject_id, teacher_id, class_id, date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$teacherSubjectId, $teacherId, $class1Id, $date, $startTime, $endTime, 'scheduled']);
    }
}

// Remove all previous $lecturers and related insertions
// --- BEGIN NEW LECTURERS LOGIC ---

// Helper: get all dates for current week (Monday to Saturday, UTC)
function getCurrentWeekDatesUTC() {
    $today = new DateTime('now', new DateTimeZone('UTC'));
    $dayOfWeek = (int)$today->format('w'); // 0=Sunday, 1=Monday, ...
    $daysSinceMonday = ($dayOfWeek + 6) % 7;
    $monday = (clone $today)->modify("-{$daysSinceMonday} days");
    $dates = [];
    for ($i = 0; $i < 6; $i++) { // Monday to Saturday
        $dates[] = (clone $monday)->modify("+{$i} days")->format('Y-m-d');
    }
    return $dates;
}

$weekDates = getCurrentWeekDatesUTC();
$timeSlots = ['07:00:00', '09:00:00', '11:00:00', '13:00:00', '15:00:00']; // UTC
$timeSlotCount = count($timeSlots);

// 1. All teachers who can teach in Class 1: 1 lecture per teacher per day (Mon-Sat) in Class 1, their subject
$class1Id = $classIds['Class 1'];
$teachersForClass1 = [];
foreach ($teacherClasses as $tc) {
    if ($tc[1] == $class1Id) {
        $teachersForClass1[] = $tc[0];
    }
}
$teachersForClass1 = array_unique($teachersForClass1);

foreach ($teachersForClass1 as $tIdx => $teacherId) {
    // Find the subject this teacher teaches in Class 1
    $teacherSubjectId = null;
    foreach ($teachers as $t) {
        if (isset($teacherIds[$t[0]]) && $teacherIds[$t[0]] == $teacherId) {
            $teacherSubjectId = $t[6]; // subject_id
            break;
        }
    }
    if (!$teacherSubjectId) continue;
    // Assign a time slot for this teacher (staggered)
    $slotIdx = $tIdx % $timeSlotCount;
    $startTime = $timeSlots[$slotIdx];
    $endTime = sprintf('%02d:00:00', (int)substr($startTime,0,2)+1);
    foreach ($weekDates as $dIdx => $date) {
        $pdo->prepare("INSERT IGNORE INTO lecturers (subject_id, teacher_id, class_id, date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$teacherSubjectId, $teacherId, $class1Id, $date, $startTime, $endTime, 'scheduled']);
    }
}

// 2. Add 5 lectures for all teachers (for their subject and their main class) in current week
foreach ($teachers as $tIdx => $t) {
    $teacherEmail = $t[0];
    $teacherId = $teacherIds[$teacherEmail];
    $subjectId = $t[6];
    $mainClassId = $t[12]; // class_teacher_of
    if (!$mainClassId) continue;
    // Assign 5 lectures on 5 different days (Mon-Fri)
    for ($i = 0; $i < 5; $i++) {
        $date = $weekDates[$i % count($weekDates)];
        $slotIdx = ($tIdx + $i) % $timeSlotCount;
        $startTime = $timeSlots[$slotIdx];
        $endTime = sprintf('%02d:00:00', (int)substr($startTime,0,2)+1);
        $pdo->prepare("INSERT IGNORE INTO lecturers (subject_id, teacher_id, class_id, date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$subjectId, $teacherId, $mainClassId, $date, $startTime, $endTime, 'scheduled']);
    }
}
// --- END NEW LECTURERS LOGIC ---

echo "Sample data inserted successfully!\n";
echo "\nDefault login credentials:\n";
echo "Principal: principal@school.com / priyasharma\n";
echo "Teachers: teacher1@school.com / amitsingh, teacher2@school.com / nehapatel, teacher3@school.com / vikramgupta, teacher4@school.com / sunitajain, teacher5@school.com / rajivmehra\n";
echo "Students: Various students with email format: firstnamelastname@school.com\n\n";
echo "Sample Data Features:\n";
echo "- 10 Classes: Class 1 to Class 10\n";
echo "- 5 students per class + 10 extra students for Class 1 (total 60 students)\n";
echo "- Admission dates: today to minus 5 days\n";
echo "- 7 Subjects: Mathematics, English, Science, Hindi, Social Studies, Computer Science, Physical Education\n";
echo "- 10 Teachers with specific class assignments\n";
echo "- Teacher 1 teaches Class 1, 2, and 3\n";
echo "- 1 exam per class for today with current time + 5 min start, + 30 min end\n";
echo "- 5 extra exams for Class 1 from tomorrow onwards\n";
echo "- Complete attendance, assignments, and schedule data\n";
echo "- Students use email for login, roll_number is unique\n\n"; 