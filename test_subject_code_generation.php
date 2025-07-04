<?php
require_once __DIR__ . '/backend/config/db.php';
require_once __DIR__ . '/backend/models/Subject.php';

echo "Testing Subject Code Generation\n";
echo "===============================\n\n";

$subject = new Subject();

// Test cases
$testCases = [
    'Mathematics' => 'MATH',
    'English Literature' => 'ENGLIT',
    'Physics' => 'PHYSIC',
    'Computer Science' => 'COMPSC',
    'History & Geography' => 'HISTGE',
    'Physical Education' => 'PHYSED',
    'Art & Music' => 'ARTMUS',
    'Biology' => 'BIOLOG',
    'Chemistry' => 'CHEMIS',
    'Economics' => 'ECONOM'
];

echo "Testing code generation for various subject names:\n";
foreach ($testCases as $subjectName => $expectedCode) {
    // Simulate the code generation logic
    $code = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $subjectName));
    $code = substr($code, 0, 6);
    
    echo sprintf("%-20s -> %s\n", $subjectName, $code);
}

echo "\nTesting with existing subjects in database:\n";
try {
    $existingSubjects = $subject->getAll();
    foreach ($existingSubjects as $subj) {
        echo sprintf("%-20s -> %s\n", $subj['name'], $subj['code']);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
?> 