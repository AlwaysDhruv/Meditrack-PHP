<?php
require "../auth/doctor_guard.php";
require "../config/db.php";
require "../auth/mailer.php";
require "../auth/pdf_helper.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: doctor_patients.php");
    exit();
}

$id = $_POST["id"];

// Fetch record + patient email
$stmt = $pdo->prepare("
    SELECT r.*, u.email AS patient_email
    FROM records r
    JOIN users u ON r.patient_id = u.id
    WHERE r.id=? AND r.doctor_id=?
");
$stmt->execute([$id, $doctor["id"]]);
$recOld = $stmt->fetch();

$title = $_POST["title"];
$description = $_POST["description"];
$bp = $_POST["bp"] ?: null;
$pulse = $_POST["pulse"] ?: null;
$temp = $_POST["temperature"] ?: null;

// UPDATE DB
$update = $pdo->prepare("
    UPDATE records SET title=?, description=?, bp=?, pulse=?, temperature=?
    WHERE id=? AND doctor_id=?
");
$update->execute([$title, $description, $bp, $pulse, $temp, $id, $doctor["id"]]);

// Prepare array for PDF
$record = [
    "title" => $title,
    "description" => $description,
    "bp" => $bp,
    "pulse" => $pulse,
    "temperature" => $temp
];

// Create PDF
$pdfFile = generateRecordPDF($record, $doctor, $recOld["patient_email"]);

// Email Patient
sendMail(
    $recOld["patient_email"],
    "Updated Medical Record",
    "<h3>Your medical record has been updated by Dr. {$doctor['name']}.</h3>",
    $pdfFile
);

header("Location: doctor_patients.php?updated=1");
exit();
?>
