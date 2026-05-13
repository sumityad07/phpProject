<?php
session_start();
require_once '../includes/db.php';

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // --- LOGIN LOGIC ---
    if ($_POST['action'] == 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            header("Location: ../login.php?error=" . urlencode("Email and Password are required."));
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Using password_verify for new hashed passwords, falling back to plain text for old demo data
            if ($user && (password_verify($password, $user['password']) || $user['password'] === $password)) { 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                if ($user['role'] == 'student') {
                    header("Location: ../student-dashboard.php");
                } elseif ($user['role'] == 'faculty') {
                    header("Location: ../faculty-dashboard.php");
                } else {
                    header("Location: ../admin-dashboard.php");
                }
                exit();
            } else {
                header("Location: ../login.php?error=" . urlencode("Invalid email or password."));
                exit();
            }
        } catch(PDOException $e) {
            header("Location: ../login.php?error=" . urlencode("System error. Please try again."));
            exit();
        }
    }
    
    // --- REGISTER LOGIC ---
    if ($_POST['action'] == 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'student';
        
        if (empty($name) || empty($email) || empty($password)) {
            header("Location: ../register.php?error=" . urlencode("All fields are required."));
            exit();
        }
        
        // Hash password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Ensure DB schema supports 'admin' role
        try {
            $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'faculty', 'admin') NOT NULL");
        } catch (Exception $e) {
            // Ignore if it fails
        }

        try {
            $pdo->beginTransaction();
            
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $pdo->rollBack();
                header("Location: ../register.php?error=" . urlencode("Email already in use."));
                exit();
            }


            // Insert into users table
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $role]);
            $user_id = $pdo->lastInsertId();

            // If student, insert into students table
            if ($role === 'student') {
                $enrollment_no = trim($_POST['enrollment_no'] ?? '');
                $department = trim($_POST['department'] ?? '');
                $semester = (int)($_POST['semester'] ?? 1);
                
                if (empty($enrollment_no)) {
                    $pdo->rollBack();
                    header("Location: ../register.php?error=" . urlencode("Enrollment number is required for students."));
                    exit();
                }
                
                // Check if enrollment number exists
                $stmt = $pdo->prepare("SELECT user_id FROM students WHERE enrollment_no = ?");
                $stmt->execute([$enrollment_no]);
                if ($stmt->fetch()) {
                    $pdo->rollBack();
                    header("Location: ../register.php?error=" . urlencode("Enrollment number already in use."));
                    exit();
                }

                $stmt = $pdo->prepare("INSERT INTO students (user_id, enrollment_no, department, semester) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $enrollment_no, $department, $semester]);
            }
            
            $pdo->commit();
            header("Location: ../login.php?success=" . urlencode("Registration successful! Please login."));
            exit();

        } catch(PDOException $e) {
            $pdo->rollBack();
            header("Location: ../register.php?error=" . urlencode("System error. Please try again."));
            exit();
        }
    }
}
?>
