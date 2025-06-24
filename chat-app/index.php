<?php
require_once 'api/config.php';
require_once 'includes/session.php';

startSecureSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Demo - Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card card">
            <div class="card__body">
                <h2 class="login-title">AI Chat Demo</h2>
                <p class="login-subtitle">Enter your passcode to continue</p>
                <form id="login-form" method="POST" action="api/login.php">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="passcode" class="form-label">Passcode</label>
                        <input type="password" id="passcode" name="passcode" class="form-control" placeholder="Enter passcode" required>
                    </div>
                    <button type="submit" class="btn btn--primary btn--full-width">Login</button>
                </form>
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">Invalid passcode. Please try again.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
