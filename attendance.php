<?php 
require_once 'includes/db.php';
require_once 'includes/header.php'; 

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle form submission for faculty and admin
if(($_SESSION['role'] == 'faculty' || $_SESSION['role'] == 'admin') && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $subject = $_POST['subject'];
    $faculty_id = $_SESSION['user_id'];
    
    // Check if attendance already marked
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND subject = ? AND faculty_id = ?");
    $stmt->execute([$date, $subject, $faculty_id]);
    
    if ($stmt->fetchColumn() > 0) {
        $message = "<div style='background: rgba(245, 158, 11, 0.1); color: #FBBF24; border: 1px solid rgba(245, 158, 11, 0.2); padding: 12px; border-radius: 8px; margin-bottom: 1.5rem;'>Attendance already marked for this date and subject.</div>";
    } else {
        if(isset($_POST['status']) && is_array($_POST['status'])) {
            $insertStmt = $pdo->prepare("INSERT INTO attendance (student_id, faculty_id, date, status, subject) VALUES (?, ?, ?, ?, ?)");
            $pdo->beginTransaction();
            try {
                foreach($_POST['status'] as $student_id => $status) {
                    $insertStmt->execute([$student_id, $faculty_id, $date, $status, $subject]);
                }
                $pdo->commit();
                $message = "<div style='background: rgba(16, 185, 129, 0.1); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.2); padding: 12px; border-radius: 8px; margin-bottom: 1.5rem;'>✅ Attendance saved successfully!</div>";
            } catch(Exception $e) {
                $pdo->rollBack();
                $message = "<div style='background: rgba(239, 68, 68, 0.1); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.2); padding: 12px; border-radius: 8px; margin-bottom: 1.5rem;'>❌ Error saving attendance.</div>";
            }
        }
    }
}
?>
<div class="container animate-fade-in">
    <div style="margin-bottom: 2.5rem;">
        <h2 style="margin-bottom: 0.5rem;">Attendance Management</h2>
        <p style="color: var(--text-muted);">View and manage student attendance records.</p>
    </div>
    
    <div class="glass-panel">
        <?php echo $message; ?>
        <?php if($_SESSION['role'] == 'faculty' || $_SESSION['role'] == 'admin'): ?>
            <h3 style="margin-bottom: 1.5rem;">Mark Daily Attendance</h3>
            <form action="" method="POST">
                <div class="grid grid-cols-2" style="gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Subject</label>
                        <select class="form-control" name="subject" required>
                            <option value="Database Systems">Database Systems</option>
                            <option value="Web Technology">Web Technology</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="table-container" style="margin-bottom: 2rem;">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT u.id, u.name, s.enrollment_no FROM users u JOIN students s ON u.id = s.user_id WHERE u.role = 'student'");
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td style="font-family: monospace; color: var(--text-muted);"><?php echo htmlspecialchars($row['enrollment_no'] ?? 'N/A'); ?></td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <select name="status[<?php echo $row['id']; ?>]" class="form-control" style="padding: 8px 12px; max-width: 150px;">
                                        <option value="present">Present</option>
                                        <option value="absent">Absent</option>
                                        <option value="late">Late</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 32px;">Save Attendance</button>
                </div>
            </form>
        <?php elseif($_SESSION['role'] == 'student'): ?>
            <h3 style="margin-bottom: 1.5rem;">My Attendance Records</h3>
            <div class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->prepare("SELECT date, subject, status FROM attendance WHERE student_id = ? ORDER BY date DESC");
                        $stmt->execute([$_SESSION['user_id']]);
                        if($stmt->rowCount() > 0):
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                $badge_class = $row['status'] == 'present' ? 'badge-success' : ($row['status'] == 'absent' ? 'badge-danger' : 'badge-warning');
                        ?>
                        <tr>
                            <td style="color: var(--text-muted);"><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">No attendance records found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <h3 style="margin-bottom: 1.5rem;">Admin View</h3>
            <p style="color: var(--text-muted);">Admins can view system-wide stats on the dashboard.</p>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
