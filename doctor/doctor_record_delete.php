<?php
session_start();
require "../auth/doctor_guard.php";
require "../config/db.php";

// Check for ID
if (!isset($_GET["id"])) {
    header("Location: doctor_patients.php");
    exit();
}

$record_id = (int) $_GET["id"];

// Ensure doctor owns the record
$stmt = $pdo->prepare("
    DELETE FROM records
    WHERE id = ? AND doctor_id = ?
");

$stmt->execute([$record_id, $doctor["id"]]);

// Redirect safely back
header("Location: doctor_patients.php?deleted=1");
exit();
?>
