<?php
// auth/patient_guard.php
require_once __DIR__ . "/session.php";

if (!is_logged_in() || $_SESSION['user']['role'] !== 'patient') {
    header("Location: /meditrack/auth/login.php");
    exit;
}

$patient = $_SESSION['user'];
