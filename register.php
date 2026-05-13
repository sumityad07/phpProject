<?php require_once 'includes/header.php'; ?>
<div class="auth-container">
    <div class="glass-panel auth-card animate-fade-in">
        <h2 style="text-align: center;">Create Account</h2>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Join SmartAnalytics today</p>
        
        <?php if(isset($_GET['error'])): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #F87171; padding: 12px; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="api/auth.php" method="POST" id="registerForm">
            <input type="hidden" name="action" value="register">
            
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="John Doe">
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="name@college.edu">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••" minlength="6">
            </div>

            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" id="roleSelect" class="form-control" required onchange="toggleStudentFields()">
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div id="studentFields">
                <div class="form-group">
                    <label class="form-label">Enrollment Number</label>
                    <input type="text" name="enrollment_no" id="enrollment_no" class="form-control" placeholder="e.g. 0101BT2026">
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" id="department" class="form-control" placeholder="e.g. Computer Science">
                </div>
                <div class="form-group">
                    <label class="form-label">Semester</label>
                    <input type="number" name="semester" id="semester" class="form-control" placeholder="e.g. 6" min="1" max="8">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 2rem;">Sign Up</button>
            
            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">Sign in</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleStudentFields() {
    const role = document.getElementById('roleSelect').value;
    const studentFields = document.getElementById('studentFields');
    const enrollInput = document.getElementById('enrollment_no');
    
    if (role === 'student') {
        studentFields.style.display = 'block';
        enrollInput.required = true;
    } else {
        studentFields.style.display = 'none';
        enrollInput.required = false;
    }
}
// Run on load
document.addEventListener('DOMContentLoaded', toggleStudentFields);
</script>

<?php require_once 'includes/footer.php'; ?>
