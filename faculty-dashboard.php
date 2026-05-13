<?php 
require_once 'includes/db.php';
require_once 'includes/header.php'; 

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

// Get Total Students
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$total_students = $stmt->fetchColumn();

// Get Class Average (Across all students and subjects)
$stmt = $pdo->query("SELECT SUM(marks_obtained) as obtained, SUM(total_marks) as total FROM marks");
$marks_data = $stmt->fetch(PDO::FETCH_ASSOC);
$class_average = $marks_data['total'] > 0 ? ($marks_data['obtained'] / $marks_data['total']) * 100 : 0;

// Calculate At-Risk Students (Very simple logic: attendance < 75% overall)
$stmt = $pdo->query("SELECT student_id, COUNT(*) as total, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present FROM attendance GROUP BY student_id");
$at_risk_count = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if($row['total'] > 0) {
        $rate = ($row['present'] / $row['total']) * 100;
        if($rate < 75) $at_risk_count++;
    }
}
?>
<div class="container animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <div>
            <h2 style="margin-bottom: 0.5rem;">Faculty Portal - <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            <p style="color: var(--text-muted);">Overview of all students and class performance.</p>
        </div>
    </div>

    <div class="grid grid-cols-3">
        <div class="glass-panel" style="border-top: 4px solid var(--primary);">
            <h3 style="color: var(--text-main);">Total Students</h3>
            <p style="font-size: 3rem; font-weight: 800; color: var(--primary); margin: 1rem 0;"><?php echo $total_students; ?></p>
        </div>
        
        <div class="glass-panel" style="border-top: 4px solid var(--danger);">
            <h3 style="color: var(--danger);">At-Risk Students</h3>
            <p style="font-size: 3rem; font-weight: 800; margin: 1rem 0;"><?php echo $at_risk_count; ?></p>
            <div class="badge badge-danger">Attendance < 75%</div>
        </div>
        
        <div class="glass-panel" style="border-top: 4px solid var(--secondary);">
            <h3 style="color: var(--secondary);">Class Average</h3>
            <p style="font-size: 3rem; font-weight: 800; margin: 1rem 0;"><?php echo number_format($class_average, 1); ?>%</p>
        </div>
    </div>
    
    <div class="grid grid-cols-2" style="margin-top: 2rem;">
        <div class="glass-panel" style="border-top: 4px solid #818CF8; background: linear-gradient(145deg, rgba(15,23,42,0.8) 0%, rgba(30,27,75,0.8) 100%);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <h3 style="color: #A5B4FC;">Gemini AI Class Analysis</h3>
                <span style="font-size: 1.5rem;">✨</span>
            </div>
            
            <div id="aiLoading" style="padding: 2rem 0; text-align: center; color: var(--text-muted);">
                <div style="display: inline-block; width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite;"></div>
                <p style="margin-top: 0.5rem; font-size: 0.9rem;">Analyzing class data...</p>
            </div>

            <div id="aiContent" style="display: none; margin-top: 1rem;">
                <p id="aiPrediction" style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;"></p>
                <div id="aiRiskFactors" style="margin-bottom: 0.5rem;"></div>
                <p id="aiRecommendation" style="color: #A5B4FC; font-size: 0.9rem; font-weight: 500;"></p>
            </div>
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        </div>
        
        <div class="glass-panel">
            <h3>Manage Academics</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.95rem;">Access quick tools for managing your classes and entering student data.</p>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <a href="attendance.php" class="btn btn-primary btn-block">Mark Attendance</a>
                <a href="marks.php" class="btn btn-primary btn-block" style="background: var(--secondary); box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.39);">Upload Marks</a>
                <a href="reports.php" class="btn btn-secondary btn-block">Generate Reports</a>
            </div>
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
                document.getElementById('aiPrediction').innerText = data.prediction || 'Class is performing normally.';
                document.getElementById('aiRecommendation').innerText = 'Recommendation: ' + (data.recommendation || '');
                
                if (data.risk_factors && data.risk_factors.length > 0) {
                    let risksHtml = '<div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">';
                    data.risk_factors.forEach(risk => {
                        risksHtml += `<span class="badge badge-warning" style="font-size: 0.7rem;">${risk}</span>`;
                    });
                    risksHtml += '</div>';
                    document.getElementById('aiRiskFactors').innerHTML = risksHtml;
                }
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
