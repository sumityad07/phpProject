<?php
require_once 'includes/db.php';
require_once 'includes/config.php';

echo "<h1 style='font-family: sans-serif; color: #4F46E5;'>System Diagnostic & Setup Tool</h1>";

// 1. Database Schema Fix
echo "<h3>1. Database Schema Alteration</h3>";
try {
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'faculty', 'admin') NOT NULL");
    echo "<p style='color: green;'>✅ Successfully ensured `admin` role exists in the `users` table ENUM.</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ ALTER TABLE command skipped or failed. It might already be altered. Error: " . $e->getMessage() . "</p>";
}

// 2. Mock Data Seeding (Attendance)
echo "<h3>2. Mock Data Seeding (Attendance)</h3>";
try {
    // Get a faculty or admin ID to use as the marker
    $stmt = $pdo->query("SELECT id FROM users WHERE role IN ('faculty', 'admin') LIMIT 1");
    $marker_id = $stmt->fetchColumn();
    
    if (!$marker_id) {
        echo "<p style='color: red;'>❌ No Faculty or Admin found to mark attendance. Please register a Faculty or Admin first.</p>";
    } else {
        // Get all students
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'student'");
        $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($students)) {
             echo "<p style='color: red;'>❌ No Students found. Please register at least one Student first.</p>";
        } else {
            $dates = [date('Y-m-d', strtotime('-1 days')), date('Y-m-d', strtotime('-2 days')), date('Y-m-d', strtotime('-3 days'))];
            $subjects = ['Database Systems', 'Web Technology'];
            $statuses = ['present', 'present', 'present', 'absent', 'late']; // 60% present bias
            
            $insertStmt = $pdo->prepare("INSERT INTO attendance (student_id, faculty_id, date, status, subject) VALUES (?, ?, ?, ?, ?)");
            $count = 0;
            
            $pdo->beginTransaction();
            foreach ($students as $student_id) {
                foreach ($dates as $date) {
                    foreach ($subjects as $subject) {
                        // Check if already exists to avoid duplicates
                        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = ? AND date = ? AND subject = ?");
                        $checkStmt->execute([$student_id, $date, $subject]);
                        if ($checkStmt->fetchColumn() == 0) {
                            $status = $statuses[array_rand($statuses)];
                            $insertStmt->execute([$student_id, $marker_id, $date, $status, $subject]);
                            $count++;
                        }
                    }
                }
            }
            $pdo->commit();
            echo "<p style='color: green;'>✅ Successfully seeded $count mock attendance records.</p>";
        }
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<p style='color: red;'>❌ Error seeding data: " . $e->getMessage() . "</p>";
}

// 3. Gemini API Diagnostic
echo "<h3>3. Gemini API Diagnostic Test</h3>";
$key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : 'Not Defined';

if ($key === 'Not Defined' || $key === 'YOUR_GEMINI_API_KEY_HERE' || empty($key)) {
    echo "<p style='color: red;'>❌ API Key is missing or default. Current value: <code>$key</code></p>";
} else {
    echo "<p>Testing API Key (first 10 chars): <code>" . substr($key, 0, 10) . "...</code></p>";
    
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $key;
    $data = [
        'contents' => [['parts' => [['text' => 'Reply with "API is working perfectly"']]]],
        'generationConfig' => ['responseMimeType' => 'application/json']
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        echo "<p style='color: red;'>❌ cURL Error during connection: $curl_error</p>";
    } else {
        echo "<p><strong>HTTP Status Code:</strong> $httpcode</p>";
        if ($httpcode == 200) {
            echo "<p style='color: green;'>✅ Connection successful! Gemini replied:</p>";
            echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($result) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ API returned an error. This usually means the key is invalid or quota is exceeded.</p>";
            echo "<p><strong>Raw Google Response:</strong></p>";
            echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd; color: red;'>" . htmlspecialchars($result) . "</pre>";
        }
    }
}

echo "<hr><a href='index.php' style='display: inline-block; padding: 10px 20px; background: #4F46E5; color: white; text-decoration: none; border-radius: 5px; font-family: sans-serif;'>Return to App</a>";
?>
