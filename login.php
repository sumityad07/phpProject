<?php require_once 'includes/header.php'; ?>
<div class="auth-container">
    <div class="glass-panel auth-card animate-fade-in">
        <h2 style="text-align: center;">Welcome Back</h2>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Sign in to continue to SmartAnalytics</p>
        
        <?php if(isset($_GET['error'])): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #F87171; padding: 12px; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['success'])): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34D399; padding: 12px; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <form action="api/auth.php" method="POST">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="name@college.edu">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 2rem;">Sign In</button>
            
            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                Don't have an account? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">Create one</a>
            </div>
        </form>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
