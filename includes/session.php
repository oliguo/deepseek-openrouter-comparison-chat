<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        ini_set('session.cookie_secure', 0);
    } else {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.use_only_cookies', 1);
}

function startSecureSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function loginUser()
{
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['csrf_token'] = generateCSRFToken();
}

function logoutUser()
{
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}

function checkSessionTimeout()
{
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            logoutUser();
            return false;
        }
    }
    return true;
}
