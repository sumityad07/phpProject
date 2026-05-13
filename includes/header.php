<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Attendance & Performance Analytics</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="nav-brand">SmartAnalytics</a>
        <div class="nav-links">
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['role'] == 'student'): ?>
                    <a href="student-dashboard.php">Dashboard</a>
                <?php elseif($_SESSION['role'] == 'faculty'): ?>
                    <a href="faculty-dashboard.php">Dashboard</a>
                <?php else: ?>
                    <a href="admin-dashboard.php">Admin Panel</a>
                <?php endif; ?>
                <a href="api/auth.php?action=logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary" style="margin-left: 20px;">Login</a>
            <?php endif; ?>
        </div>
    </nav>
