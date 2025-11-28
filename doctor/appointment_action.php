<?php
require "../auth/doctor_guard.php";
require "../config/db.php";
require "../auth/mailer.php";

/* Validate */
if (!isset($_GET["id"]) || !isset($_GET["action"])) {
    header("Location: doctor_appointments.php?error=invalid");
    exit();
}

$id = (int) $_GET["id"];
$action = strtolower(trim($_GET["action"]));

if (!in_array($action, ["accept", "reject", "complete"])) {
    header("Location: doctor_appointments.php?error=invalid_action");
    exit();
}

/* Fetch appointment */
$stmt = $pdo->prepare("
    SELECT a.*, u.name AS patient_name, u.email AS patient_email
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.id=? AND a.doctor_id=?
");
$stmt->execute([$id, $doctor['id']]);
$app = $stmt->fetch();

if (!$app) {
    header("Location: doctor_appointments.php?error=not_found");
    exit();
}

/* Determine status + email content */
switch ($action) {

    case "accept":
        $status = "accepted";
        $emailBody = "
            <h2>Appointment Accepted</h2>
            <p>Your appointment with Dr. {$doctor['name']} has been accepted.</p>
        ";
        break;

    case "reject":
        $status = "rejected";
        $emailBody = "
            <h2>Appointment Rejected</h2>
            <p>Your appointment with Dr. {$doctor['name']} has been rejected.</p>
        ";
        break;

    case "complete":
        $status = "completed";
        $emailBody = "
            <h2>Appointment Completed</h2>
            <p>Your appointment with Dr. {$doctor['name']} has been marked as completed.</p>
        ";
        break;

}

/* Update DB */
$update = $pdo->prepare("UPDATE appointments SET status=? WHERE id=?");
$update->execute([$status, $id]);

/* Send email to patient */
sendMail(
    $app["patient_email"],
    "Appointment " . ucfirst($status),
    $emailBody
);

header("Location: doctor_appointments.php?success=" . $status);
exit();
?>
