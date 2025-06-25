<?php
require_once '../api/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../index.php?error=1');
    exit();
}

$passcode = sanitizeInput($_POST['passcode'] ?? '');

if ($passcode === LOGIN_PASSCODE) {
    loginUser();
    header('Location: ../home.php');
} else {
    header('Location: ../index.php?error=1');
}
exit();
?>
