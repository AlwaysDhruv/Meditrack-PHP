<?php
// home.php
require_once __DIR__ . "/auth/session.php";

if (!is_logged_in()) {
    header("Location: /meditrack/auth/login.php");
    exit;
}

if ($_SESSION['user']['role'] === 'patient') {
    header("Location: /meditrack/patient/patient_home.php");
} else {
    header("Location: /meditrack/doctor/patient_home.php");
}
exit;
