<?php
require "../auth/patient_guard.php";
require "../config/db.php";
require "../auth/mailer.php";

if (!isset($_GET["doctor"])) {
    header("Location: patient_doctors.php");
    exit();
}

$doctorId = (int) $_GET["doctor"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=? AND role='doctor'");
$stmt->execute([$doctorId]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    header("Location: patient_doctors.php?error=invalid_doctor");
    exit();
}

$message = "";
$error = "";

/* ===============================================================
   HANDLE APPOINTMENT CREATION
================================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $date   = trim($_POST["date"]);
    $reason = trim($_POST["reason"]);

    $selected = strtotime($date);
    $now = time();

    /* Prevent PAST dates or times */
    if ($selected <= $now) {
        $error = "You cannot select a past date or old time.";
    } else {

        /* Prevent double-booking */
        $check = $pdo->prepare("
            SELECT id FROM appointments 
            WHERE doctor_id = ? AND date = ?
        ");
        $check->execute([$doctorId, $date]);

        if ($check->rowCount() > 0) {
            $error = "This time slot is already booked. Please choose another time.";
        } else {

            /* Insert appointment */
            $insert = $pdo->prepare("
                INSERT INTO appointments (patient_id, doctor_id, date, reason, status)
                VALUES (?, ?, ?, ?, 'requested')
            ");

            if ($insert->execute([$patient['id'], $doctorId, $date, $reason])) {

                // SEND MAIL TO DOCTOR
                sendMail(
                    $doctor['email'],
                    "New Appointment Request",
                    "
                    <h2>New Appointment Request</h2>
                    <p>You received a request from <strong>{$patient['name']}</strong>.</p>
                    <p><strong>Date:</strong> ".date('d M Y, h:i A', strtotime($date))."</p>
                    <p><strong>Reason:</strong> {$reason}</p>
                    "
                );

                // SEND MAIL TO PATIENT
                sendMail(
                    $patient['email'],
                    "Appointment Request Sent",
                    "
                    <h2>Your Appointment Request</h2>
                    <p>You requested an appointment with <strong>Dr. {$doctor['name']}</strong>.</p>
                    <p><strong>Status:</strong> Requested</p>
                    "
                );

                $message = "Your appointment request has been sent!";
            } else {
                $error = "Error sending appointment request.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment | MediTrack</title>

    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/style.css">

<style>
/* CARD */
.form-card {
    padding: 28px;
    max-width: 550px;
    border-radius: 14px;
    margin-top: 25px;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(12px);
    box-shadow: 0 10px 35px rgba(0,0,0,0.4);
}

/* BIG INPUT FIELDS */
.form-input {
    width: 100%;
    padding: 16px;
    border-radius: 10px;
    background: rgba(255,255,255,0.18);
    border: none;
    margin-bottom: 18px;
    font-size: 17px;
    color: white;
}

/* FIX DATETIME-LOCAL SIZE */
input[type="datetime-local"] {
    height: 55px;
    font-size: 17px;
}

/* TEXTAREA */
textarea.form-input {
    height: 140px;
}

/* BUTTON */
.btn-submit {
    width: 100%;
    padding: 16px;
    border-radius: 10px;
    background: rgba(0,200,255,0.50);
    border: none;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
}
.btn-submit:hover {
    background: rgba(0,200,255,0.70);
}

/* ALERT */
.success-box {
    padding: 14px;
    border-radius: 8px;
    background: rgba(46,204,113,0.25);
    margin-top: 10px;
    font-weight: bold;
}
.error-box {
    padding: 14px;
    border-radius: 8px;
    background: rgba(255,60,60,0.25);
    margin-top: 10px;
    font-weight: bold;
}
</style>

<script>
// BLOCK PAST DATES/TIMES IN UI
window.addEventListener("DOMContentLoaded", () => {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

    document.getElementById("date").min = now.toISOString().slice(0, 16);
});
</script>

</head>

<body>

<div class="patient-panel">

<?php include "sidebar.php"; ?>

<div class="patient-content">

    <h1>📅 Book Appointment</h1>
    <p>Doctor: <strong>Dr. <?php echo htmlspecialchars($doctor["name"]); ?></strong></p>

    <?php if ($message): ?>
        <div class="success-box"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error-box"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">

            <label>Date & Time</label>
            <input type="datetime-local" id="date" name="date" class="form-input" required>

            <label>Reason</label>
            <textarea name="reason" class="form-input" placeholder="Describe your symptoms or request..." required></textarea>

            <button class="btn-submit">Submit Request</button>

        </form>
    </div>

</div>

</div>

</body>
</html>
