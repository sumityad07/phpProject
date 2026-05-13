<?php 
require_once 'includes/db.php';
require_once 'includes/header.php'; 

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Calculate Attendance Rate
$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present FROM attendance WHERE student_id = ?");
$stmt->execute([$student_id]);
$attendance_data = $stmt->fetch(PDO::FETCH_ASSOC);
$attendance_rate = $attendance_data['total'] > 0 ? ($attendance_data['present'] / $attendance_data['total']) * 100 : 0;
$attendance_status = $attendance_rate >= 75 ? 'Safe' : 'At Risk';
$attendance_color = $attendance_rate >= 75 ? 'var(--secondary)' : 'var(--danger)';

// Calculate Average Marks
$stmt = $pdo->prepare("SELECT SUM(marks_obtained) as obtained, SUM(total_marks) as total FROM marks WHERE student_id = ?");
$stmt->execute([$student_id]);
$marks_data = $stmt->fetch(PDO::FETCH_ASSOC);
$average_marks = $marks_data['total'] > 0 ? ($marks_data['obtained'] / $marks_data['total']) * 100 : 0;
$marks_status = $average_marks >= 60 ? 'Good' : 'Needs Improvement';
$marks_color = $average_marks >= 60 ? 'var(--primary)' : 'var(--warning)';

// Mock AI Prediction logic based on actual data
$predicted_grade = 'B';
if($average_marks >= 80 && $attendance_rate >= 80) $predicted_grade = 'A';
if($average_marks < 50 || $attendance_rate < 60) $predicted_grade = 'C';

?>
<div class="container animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <div>
            <h2 style="margin-bottom: 0.5rem;">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</h2>
            <p style="color: var(--text-muted);">Here is your academic overview for this semester.</p>
        </div>
    </div>

    <div class="grid grid-cols-3">
        <div class="glass-panel" style="border-top: 4px solid <?php echo $attendance_color; ?>;">
            <h3 style="color: <?php echo $attendance_color; ?>;">Attendance Rate</h3>
            <p style="font-size: 3rem; font-weight: 800; margin: 1rem 0;"><?php echo number_format($attendance_rate, 1); ?>%</p>
            <div class="badge <?php echo $attendance_rate >= 75 ? 'badge-success' : 'badge-danger'; ?>">
                Status: <?php echo $attendance_status; ?>
            </div>
        </div>
        
        <div class="glass-panel" style="border-top: 4px solid <?php echo $marks_color; ?>;">
            <h3 style="color: <?php echo $marks_color; ?>;">Average Marks</h3>
            <p style="font-size: 3rem; font-weight: 800; margin: 1rem 0;"><?php echo number_format($average_marks, 1); ?>%</p>
            <div class="badge <?php echo $average_marks >= 60 ? 'badge-success' : 'badge-warning'; ?>">
                Status: <?php echo $marks_status; ?>
            </div>
        </div>

        <div class="glass-panel" style="border-top: 4px solid var(--primary); background: linear-gradient(145deg, rgba(15,23,42,0.8) 0%, rgba(30,27,75,0.8) 100%);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <h3 style="color: #A5B4FC;">Gemini AI Insight</h3>
                <span style="font-size: 1.5rem;">✨</span>
            </div>
            
            <div id="aiLoading" style="padding: 2rem 0; text-align: center; color: var(--text-muted);">
                <div style="display: inline-block; width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite;"></div>
                <p style="margin-top: 0.5rem; font-size: 0.9rem;">Analyzing your performance...</p>
            </div>

            <div id="aiContent" style="display: none; margin-top: 1rem;">
                <p id="aiPrediction" style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;"></p>
                <p id="aiRecommendation" style="color: var(--text-muted); font-size: 0.9rem;"></p>
            </div>
            
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        </div>
    </div>
    
    <div style="margin-top: 3rem;" class="glass-panel">
        <h3>Quick Actions</h3>
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <a href="attendance.php" class="btn btn-primary">View Detailed Attendance</a>
            <a href="reports.php" class="btn btn-secondary">View Analytics Report</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('api/ai_features.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('aiLoading').style.display = 'none';
            const aiContent = document.getElementById('aiContent');
            aiContent.style.display = 'block';
            
            if(data.error) {
                document.getElementById('aiPrediction').innerText = 'AI Unavailable';
                document.getElementById('aiRecommendation').innerText = data.error;
            } else {
                document.getElementById('aiPrediction').innerText = data.prediction || 'Keep up the good work!';
                document.getElementById('aiRecommendation').innerText = data.recommendation || '';
            }
        })
        .catch(error => {
            document.getElementById('aiLoading').style.display = 'none';
            document.getElementById('aiContent').style.display = 'block';
            document.getElementById('aiPrediction').innerText = 'Error loading AI insights.';
        });
});
</script>

<?php require_once 'includes/footer.php'; ?>
