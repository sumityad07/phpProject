<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$response = [
    'attendance_labels' => [],
    'attendance_data' => [],
    'performance_labels' => [],
    'performance_data' => []
];

if($role == 'student') {
    // Attendance per subject
    $stmt = $pdo->prepare("SELECT subject, COUNT(*) as total, SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) as present FROM attendance WHERE student_id = ? GROUP BY subject");
    $stmt->execute([$user_id]);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response['attendance_labels'][] = $row['subject'];
        $rate = $row['total'] > 0 ? ($row['present']/$row['total']) * 100 : 0;
        $response['attendance_data'][] = round($rate, 2);
    }
    
    // Performance per exam
    $stmt = $pdo->prepare("SELECT exam_type, subject, (marks_obtained/total_marks)*100 as percentage FROM marks WHERE student_id = ? ORDER BY date_uploaded ASC");
    $stmt->execute([$user_id]);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response['performance_labels'][] = $row['subject'] . ' ' . $row['exam_type'];
        $response['performance_data'][] = round($row['percentage'], 2);
    }
} else {
    // Faculty: Attendance across class per day
    $stmt = $pdo->query("SELECT date, COUNT(*) as total, SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) as present FROM attendance GROUP BY date ORDER BY date ASC LIMIT 5");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response['attendance_labels'][] = $row['date'];
        $rate = $row['total'] > 0 ? ($row['present']/$row['total']) * 100 : 0;
        $response['attendance_data'][] = round($rate, 2);
    }
    
    // Performance per subject class average
    $stmt = $pdo->query("SELECT subject, SUM(marks_obtained) as obtained, SUM(total_marks) as total FROM marks GROUP BY subject");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response['performance_labels'][] = $row['subject'];
        $rate = $row['total'] > 0 ? ($row['obtained']/$row['total']) * 100 : 0;
        $response['performance_data'][] = round($rate, 2);
    }
}

echo json_encode($response);
?>
