<?php 
require_once 'includes/header.php'; 
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<div class="container animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <div>
            <h2 style="margin-bottom: 0.5rem;">Performance Analytics & AI Insights</h2>
            <p style="color: var(--text-muted);">Deep dive into academic trends and algorithmic predictions.</p>
        </div>
        <button class="btn btn-primary" onclick="generateAIReport()" style="gap: 8px;">
            <span>✨</span> Generate AI Report
        </button>
    </div>

    <div class="grid grid-cols-2" style="margin-bottom: 2rem;">
        <div class="glass-panel">
            <h3 style="margin-bottom: 1.5rem;">Attendance Trends</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
        <div class="glass-panel">
            <h3 style="margin-bottom: 1.5rem;">Performance Matrix</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="glass-panel" style="border-top: 4px solid #C084FC; background: linear-gradient(145deg, rgba(15,23,42,0.8) 0%, rgba(46,16,101,0.5) 100%);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="color: #E9D5FF; margin: 0; display: flex; align-items: center; gap: 10px;">
                🤖 Actionable Insights
            </h3>
        </div>
        
        <div id="ai-insights" style="background: rgba(0,0,0,0.2); padding: 24px; border-radius: 12px; border-left: 4px solid #C084FC;">
            <p style="color: var(--text-muted); font-size: 1.1rem; text-align: center; margin: 2rem 0;">
                Click "Generate AI Report" to fetch real-time predictions and risk factors from Gemini.
            </p>
        </div>
        <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
    </div>
</div>

<script src="assets/js/charts.js"></script>
<?php require_once 'includes/footer.php'; ?>
