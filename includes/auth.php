
<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function getUserData() {
    return $_SESSION['user'] ?? null;
}
?>