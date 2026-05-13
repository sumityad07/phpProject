<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'update_gemini_key') {
        $new_key = trim($_POST['api_key'] ?? '');
        
        if (empty($new_key)) {
            header("Location: ../admin-dashboard.php?error=" . urlencode("API Key cannot be empty."));
            exit();
        }

        $config_file = '../includes/config.php';
        
        if (!file_exists($config_file)) {
            header("Location: ../admin-dashboard.php?error=" . urlencode("Config file not found."));
            exit();
        }

        $config_content = file_get_contents($config_file);
        
        // Use regex to replace the existing key safely
        $pattern = "/define\('GEMINI_API_KEY',\s*'.*?'\);/";
        $replacement = "define('GEMINI_API_KEY', '" . addslashes($new_key) . "');";
        
        $new_config_content = preg_replace($pattern, $replacement, $config_content);
        
        if ($new_config_content !== null) {
            if (file_put_contents($config_file, $new_config_content) !== false) {
                header("Location: ../admin-dashboard.php?success=" . urlencode("Gemini API Key updated successfully!"));
            } else {
                header("Location: ../admin-dashboard.php?error=" . urlencode("Failed to write to config.php. Check file permissions."));
            }
        } else {
            header("Location: ../admin-dashboard.php?error=" . urlencode("Failed to parse config file."));
        }
        exit();
    }
}

header("Location: ../admin-dashboard.php");
exit();
?>
