<?php
// auth/session.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// returns array|false
function current_user() {
    return $_SESSION['user'] ?? false;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}
