<?php 
require_once 'includes/db.php';
require_once 'includes/header.php'; 

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

if(($_SESSION['role'] == 'faculty' || $_SESSION['role'] == 'admin') && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = $_POST['subject'];
    $exam_type = $_POST['exam_type'];
    $total_marks = $_POST['total_marks'];
    $faculty_id = $_SESSION['user_id'];
    
    if(isset($_POST['marks']) && is_array($_POST['marks'])) {
        $insertStmt = $pdo->prepare("INSERT INTO marks (student_id, faculty_id, subject, exam_type, marks_obtained, total_marks) VALUES (?, ?, ?, ?, ?, ?)");
        $pdo->beginTransaction();
        try {
            foreach($_POST['marks'] as $student_id => $marks_obtained) {
                if($marks_obtained !== '') {
                    $insertStmt->execute([$student_id, $faculty_id, $subject, $exam_type, $marks_obtained, $total_marks]);
                }
            }
            $pdo->commit();
            $message = "<div style='background: rgba(16, 185, 129, 0.1); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.2); padding: 12px; border-radius: 8px; margin-bottom: 1.5rem;'>✅ Marks uploaded successfully!</div>";
        } catch(Exception $e) {
            $pdo->rollBack();
            $message = "<div style='background: rgba(239, 68, 68, 0.1); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.2); padding: 12px; border-radius: 8px; margin-bottom: 1.5rem;'>❌ Error saving marks: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<div class="container animate-fade-in">
    <div style="margin-bottom: 2.5rem;">
        <h2 style="margin-bottom: 0.5rem;">Marks & Assessment</h2>
        <p style="color: var(--text-muted);">View and manage student grades and assessments.</p>
    </div>
    
    <div class="glass-panel">
        <?php echo $message; ?>
        <?php if($_SESSION['role'] == 'faculty' || $_SESSION['role'] == 'admin'): ?>
            <h3 style="margin-bottom: 1.5rem;">Upload Assessment Marks</h3>
            <form action="" method="POST">
                <div class="grid grid-cols-3" style="gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Subject</label>
                        <select class="form-control" name="subject" required>
                            <option value="Database Systems">Database Systems</option>
                            <option value="Web Technology">Web Technology</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Exam Type</label>
                        <select class="form-control" name="exam_type" required>
                            <option value="Mid Term">Mid Term</option>
                            <option value="Finals">Finals</option>
                            <option value="Assignment">Assignment</option>
                            <option value="Quiz 1">Quiz 1</option>
                            <option value="Quiz 2">Quiz 2</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Total Marks</label>
                        <input type="number" name="total_marks" class="form-control" value="100" required>
                    </div>
                </div>
                
                <div class="table-container" style="margin-bottom: 2rem;">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Marks Obtained</th>
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
                                    <input type="number" step="0.01" name="marks[<?php echo $row['id']; ?>]" class="form-control" style="padding: 8px 12px; max-width: 200px;" placeholder="Leave empty if absent">
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 32px;">Save Marks</button>
                </div>
            </form>
        <?php elseif($_SESSION['role'] == 'student'): ?>
            <h3 style="margin-bottom: 1.5rem;">My Academic Progress</h3>
            <div class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Exam</th>
                            <th>Marks</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->prepare("SELECT subject, exam_type, marks_obtained, total_marks FROM marks WHERE student_id = ? ORDER BY date_uploaded DESC");
                        $stmt->execute([$_SESSION['user_id']]);
                        if($stmt->rowCount() > 0):
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                $percentage = ($row['marks_obtained'] / $row['total_marks']) * 100;
                                $badge_class = $percentage >= 75 ? 'badge-success' : ($percentage >= 50 ? 'badge-warning' : 'badge-danger');
                        ?>
                        <tr>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['exam_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['marks_obtained']) . ' / ' . htmlspecialchars($row['total_marks']); ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo number_format($percentage, 2); ?>%</span></td>
                        </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">No marks recorded yet.</td>
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
