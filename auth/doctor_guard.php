<?php
// auth/doctor_guard.php
require_once __DIR__ . "/session.php";

if (!is_logged_in() || $_SESSION['user']['role'] !== 'doctor') {
    header("Location: /meditrack/auth/login.php");
    exit;
}

$doctor = $_SESSION['user'];
