<?php
require_once 'config.php';
require_once '../includes/session.php';

startSecureSession();
logoutUser();

header('Location: ../index.php');
exit();
?>
