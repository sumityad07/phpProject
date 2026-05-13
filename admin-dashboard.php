<?php 
require_once 'includes/db.php';
require_once 'includes/header.php'; 

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get system stats
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$total_students = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'faculty'");
$total_faculty = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM attendance");
$total_attendance_records = $stmt->fetchColumn();

// Read current API key (obfuscated for display)
require_once 'includes/config.php';
$current_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
$display_key = strlen($current_key) > 10 ? substr($current_key, 0, 5) . '...' . substr($current_key, -5) : 'Not Set';
if ($current_key === 'YOUR_GEMINI_API_KEY_HERE') $display_key = 'Not Set (Default Placeholder)';
?>

<div class="container animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <div>
            <h2 style="margin-bottom: 0.5rem;">Admin Dashboard</h2>
            <p style="color: var(--text-muted);">Manage system configuration and view overarching stats.</p>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34D399; padding: 16px; border-radius: 12px; margin-bottom: 2rem; font-weight: 500;">
            ✅ <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #F87171; padding: 16px; border-radius: 12px; margin-bottom: 2rem; font-weight: 500;">
            ❌ <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-3" style="margin-bottom: 2rem;">
        <div class="glass-panel" style="border-top: 4px solid var(--primary);">
            <h3 style="color: var(--text-main);">Total Students</h3>
            <p style="font-size: 3rem; font-weight: 800; color: var(--primary); margin: 1rem 0;"><?php echo $total_students; ?></p>
        </div>
        
        <div class="glass-panel" style="border-top: 4px solid var(--secondary);">
            <h3 style="color: var(--secondary);">Total Faculty</h3>
            <p style="font-size: 3rem; font-weight: 800; margin: 1rem 0;"><?php echo $total_faculty; ?></p>
        </div>
        
        <div class="glass-panel" style="border-top: 4px solid #A5B4FC;">
            <h3 style="color: #A5B4FC;">Attendance Records</h3>
            <p style="font-size: 3rem; font-weight: 800; margin: 1rem 0;"><?php echo $total_attendance_records; ?></p>
        </div>
    </div>
    
    <div class="grid grid-cols-2">
        <div class="glass-panel" style="border: 1px solid rgba(99, 102, 241, 0.3);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem;">
                <span style="font-size: 1.5rem;">🔑</span>
                <h3 style="margin-bottom: 0;">Gemini API Configuration</h3>
            </div>
            
            <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin-bottom: 1.5rem;">
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Current Key Status:</p>
                <code style="background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 4px; color: #A5B4FC; font-size: 1.1rem;"><?php echo $display_key; ?></code>
            </div>

            <form action="api/admin_actions.php" method="POST">
                <input type="hidden" name="action" value="update_gemini_key">
                <div class="form-group">
                    <label class="form-label">Update API Key</label>
                    <input type="password" name="api_key" class="form-control" required placeholder="Paste new Google Gemini API key here">
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">This will update the `includes/config.php` file securely.</p>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Update API Key</button>
            </form>
        </div>
        
        <div class="glass-panel">
            <h3>System Health</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Automated checks to ensure the application is running smoothly.</p>
            
            <ul style="list-style: none;">
                <li style="padding: 12px 0; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between;">
                    <span>Database Connection</span>
                    <span class="badge badge-success">Online</span>
                </li>
                <li style="padding: 12px 0; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between;">
                    <span>User Authentication</span>
                    <span class="badge badge-success">Active</span>
                </li>
                <li style="padding: 12px 0; display: flex; justify-content: space-between;">
                    <span>AI Model Integration</span>
                    <?php if($current_key !== 'YOUR_GEMINI_API_KEY_HERE' && !empty($current_key)): ?>
                        <span class="badge badge-success">Configured</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Not Configured</span>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="glass-panel" style="margin-top: 2rem;">
        <h3>Manage Academics</h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.95rem;">Access quick tools for managing classes and viewing system-wide reports.</p>
        <div style="display: flex; gap: 1rem;">
            <a href="attendance.php" class="btn btn-primary">Mark Attendance</a>
            <a href="marks.php" class="btn btn-primary" style="background: var(--secondary); box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.39);">Upload Marks</a>
            <a href="reports.php" class="btn btn-secondary">Generate Reports</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
