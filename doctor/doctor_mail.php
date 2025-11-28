<?php
require "../auth/doctor_guard.php";
require "../config/db.php";
require "../auth/mailer.php";

// Fetch patients
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.name, u.email
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = ?
");
$stmt->execute([$doctor["id"]]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = "";
$error = "";

// SEND MAIL
if (isset($_POST["send_mail"])) {

    $patient_id = $_POST["patient_id"];
    $subject    = $_POST["subject"];
    $message    = $_POST["message"];

    $pstmt = $pdo->prepare("SELECT email, name FROM users WHERE id=?");
    $pstmt->execute([$patient_id]);
    $patient = $pstmt->fetch();

    if (!$patient) {
        $error = "Patient not found.";
    } else {
        $email = $patient["email"];

        $attachment = null;
        if (!empty($_FILES["attachment"]["name"])) {
            $dir = "../uploads/mails/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $path = $dir . time() . "_" . basename($_FILES["attachment"]["name"]);
            move_uploaded_file($_FILES["attachment"]["tmp_name"], $path);

            $attachment = $path;
        }

        try {
            sendMail(
                $email,
                $subject,
                "<h3>Message from Dr. {$doctor['name']}:</h3><p>$message</p>",
                $attachment
            );
            $success = "Email sent successfully!";
        } catch (Exception $e) {
            $error = "Failed to send email.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Send Mail | MediTrack</title>

<link rel="stylesheet" href="../assets/css/panel.css">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
/* CENTER MAIL CARD */
.mail-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.mail-card {
    padding: 28px;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(10px);
    border-radius: 14px;
    width: 750px; /* wider */
    box-shadow: 0 10px 38px rgba(0,0,0,0.35);
}

/* HEADINGS */
.mail-card h2 {
    margin-bottom: 20px;
    font-weight: bold;
}

/* INPUTS FIXED */
.form-input {
    width: 100%;
    padding: 14px;
    border-radius: 8px;
    border: none;
    background: rgba(255,255,255,0.15);
    color: #fff;
    margin-bottom: 18px;
}

textarea.form-input {
    height: 160px;
}

/* BUTTON */
.submit-btn {
    width: 100%;
    padding: 14px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    background: rgba(46,204,113,0.55);
    cursor: pointer;
}

.submit-btn:hover {
    background: rgba(46,204,113,0.75);
}

/* ALERTS */
.success, .error {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: 600;
}

.success { background: rgba(46,204,113,0.45); }
.error { background: rgba(231,76,60,0.45); }

</style>
</head>

<body>

<div class="patient-panel">

<?php include "sidebar_doctor.php"; ?>

<!-- CONTENT -->
<div class="patient-content">

<h1>✉️ Send Mail to Patient</h1>

<div class="mail-wrapper">
<div class="mail-card">

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <h2>Compose Email</h2>

        <label>Patient</label>
        <select name="patient_id" class="form-input" required>
            <option value="">Select patient...</option>
            <?php foreach ($patients as $p): ?>
                <option value="<?php echo $p["id"]; ?>">
                    <?php echo $p["name"]; ?> — <?php echo $p["email"]; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Subject</label>
        <input type="text" name="subject" class="form-input" placeholder="Enter email subject..." required>

        <label>Message</label>
        <textarea name="message" class="form-input" placeholder="Write your message..." required></textarea>

        <label>Attachment (Optional)</label>
        <input type="file" name="attachment" class="form-input">

        <button class="submit-btn" name="send_mail">Send Email</button>

    </form>

</div>
</div>

</div>
</div>

</body>
</html>
