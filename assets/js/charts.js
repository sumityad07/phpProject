document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await fetch('api/chart_data.php');
        const data = await response.json();
        
        // Attendance Chart
        const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctxAttendance, {
            type: 'line',
            data: {
                labels: data.attendance_labels && data.attendance_labels.length > 0 ? data.attendance_labels : ['No Data'],
                datasets: [{
                    label: 'Attendance %',
                    data: data.attendance_data && data.attendance_data.length > 0 ? data.attendance_data : [0],
                    borderColor: '#818CF8',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(129, 140, 248, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94A3B8' } } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94A3B8' }, beginAtZero: true },
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94A3B8' } }
                }
            }
        });

        // Performance Chart
        const ctxPerformance = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctxPerformance, {
            type: 'bar',
            data: {
                labels: data.performance_labels && data.performance_labels.length > 0 ? data.performance_labels : ['No Data'],
                datasets: [{
                    label: 'Scores %',
                    data: data.performance_data && data.performance_data.length > 0 ? data.performance_data : [0],
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    hoverBackgroundColor: '#34D399',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94A3B8' } } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94A3B8' }, beginAtZero: true },
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94A3B8' } }
                }
            }
        });
    } catch(err) {
        console.error("Failed to load chart data:", err);
    }
});

async function generateAIReport() {
    const insightsDiv = document.getElementById('ai-insights');
    
    // Loading state
    insightsDiv.innerHTML = `
        <div style="text-align: center; padding: 2rem 0; color: var(--text-muted);">
            <div style="display: inline-block; width: 30px; height: 30px; border: 3px solid rgba(255,255,255,0.2); border-radius: 50%; border-top-color: #C084FC; animation: spin 1s ease-in-out infinite; margin-bottom: 1rem;"></div>
            <p>Analyzing extensive data patterns...</p>
        </div>
    `;
    
    try {
        const response = await fetch('api/ai_features.php');
        const data = await response.json();
        
        if (data.error) {
            insightsDiv.innerHTML = `
                <div style="color: #F87171; background: rgba(239,68,68,0.1); padding: 15px; border-radius: 8px;">
                    <strong>AI Unavailable:</strong> ${data.error}
                </div>
            `;
            return;
        }

        let risksHtml = '';
        if (data.risk_factors && data.risk_factors.length > 0) {
            risksHtml = `<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px;">`;
            data.risk_factors.forEach(risk => {
                risksHtml += `<span class="badge badge-warning">${risk}</span>`;
            });
            risksHtml += `</div>`;
        } else {
            risksHtml = '<p style="color: var(--text-muted); margin-bottom: 15px;">No immediate risk factors identified.</p>';
        }

        insightsDiv.innerHTML = `
            <div style="margin-bottom: 1.5rem;">
                <h4 style="color: var(--text-main); margin-bottom: 0.5rem; font-size: 1.1rem;">🔮 Prediction Summary</h4>
                <p style="font-size: 1.2rem; color: #E9D5FF; font-weight: 500;">${data.prediction}</p>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <h4 style="color: var(--text-main); margin-bottom: 0.5rem; font-size: 1.1rem;">⚠️ Identified Risk Factors</h4>
                ${risksHtml}
            </div>
            
            <div>
                <h4 style="color: var(--text-main); margin-bottom: 0.5rem; font-size: 1.1rem;">💡 Strategic Recommendation</h4>
                <p style="color: #34D399; font-weight: 500; font-size: 1.1rem; line-height: 1.5;">${data.recommendation}</p>
            </div>
        `;
    } catch (error) {
        insightsDiv.innerHTML = `
            <div style="color: #F87171; background: rgba(239,68,68,0.1); padding: 15px; border-radius: 8px;">
                <strong>Connection Error:</strong> Failed to reach the AI service.
            </div>
        `;
    }
}
