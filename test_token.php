<?php
require_once 'backend/config/db.php';

try {
    $db = db();
    echo "Database connection successful\n";
    
    // Test token creation
    $email = 'test@example.com';
    $userType = 'principal';
    $token = bin2hex(random_bytes(32));
    $expiresAt = gmdate('Y-m-d H:i:s', time() + 3600);
    
    echo "Generated token: $token\n";
    echo "Expires at: $expiresAt\n";
    
    // Insert token
    $stmt = $db->query("INSERT INTO password_resets (email, user_type, token, expires_at) VALUES (?, ?, ?, ?)", 
        [$email, $userType, $token, $expiresAt]);
    
    echo "Token inserted successfully\n";
    
    // Print MySQL NOW() and expires_at for this token
    $stmt = $db->query("SELECT NOW() as mysql_now, expires_at FROM password_resets WHERE token = ?", [$token]);
    $row = $stmt->fetch();
    echo "MySQL NOW(): " . $row['mysql_now'] . "\n";
    echo "expires_at in DB: " . $row['expires_at'] . "\n";
    
    // Test token retrieval
    $stmt = $db->query("SELECT * FROM password_resets WHERE token = ? AND email = ? AND user_type = ? AND expires_at > NOW() AND used = 0", 
        [$token, $email, $userType]);
    $resetRecord = $stmt->fetch();
    
    if ($resetRecord) {
        echo "Token validation successful\n";
        print_r($resetRecord);
    } else {
        echo "Token validation failed\n";
        
        // Check what's in the database
        $stmt = $db->query("SELECT * FROM password_resets WHERE token = ?", [$token]);
        $allRecords = $stmt->fetchAll();
        echo "All records with this token:\n";
        print_r($allRecords);
    }
    
    // Clean up
    $db->query("DELETE FROM password_resets WHERE email = ?", [$email]);
    echo "Test completed\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 