<?php
require "../auth/patient_guard.php";
require "../config/db.php";
require "../auth/mailer.php";

$success = "";
$error = "";

/* ============================================================
   FETCH DOCTORS WHO PATIENT HAD APPOINTMENTS WITH
============================================================ */
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.name, u.email, u.specialization
    FROM appointments a
    JOIN users u ON a.doctor_id = u.id
    WHERE a.patient_id = ?
");
$stmt->execute([$patient["id"]]);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   SEND MAIL
============================================================ */
if (isset($_POST["send_mail"])) {

    $doctor_id = $_POST["doctor_id"];
    $subject   = trim($_POST["subject"]);
    $message   = trim($_POST["message"]);

    $dstmt = $pdo->prepare("SELECT name, email FROM users WHERE id=?");
    $dstmt->execute([$doctor_id]);
    $doctor = $dstmt->fetch();

    if (!$doctor) {
        $error = "Doctor not found.";
    } else {

        $attachment = null;

        if (!empty($_FILES["attachment"]["name"])) {
            $dir = "../uploads/patient_mail/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $filePath = $dir . time() . "_" . basename($_FILES["attachment"]["name"]);
            move_uploaded_file($_FILES["attachment"]["tmp_name"], $filePath);

            $attachment = $filePath;
        }

        try {
            sendMail(
                $doctor["email"],
                $subject,
                "<h3>Message from Patient: {$patient['name']}</h3><p>$message</p>",
                $attachment
            );
            $success = "Mail sent successfully!";
        } catch (Exception $e) {
            $error = "Failed to send mail.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mail Doctor | MediTrack</title>

<link rel="stylesheet" href="../assets/css/panel.css">
<link rel="stylesheet" href="../assets/css/style.css">

<style>

/* PAGE LAYOUT */
.mail-container {
    width: 100%;
    display: flex;
    justify-content: center;
}
.mail-card {
    width: 750px;
    background: rgba(255,255,255,0.12);
    padding: 28px;
    border-radius: 14px;
    margin-top: 25px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.4);
    backdrop-filter: blur(12px);
}

/* FORM INPUTS */
.form-input {
    width: 100%;
    padding: 14px;
    border-radius: 8px;
    border: none;
    margin-bottom: 18px;
    color: #fff;
    background: rgba(255,255,255,0.18);
}

textarea.form-input {
    height: 150px;
}

/* FIX: Dropdown background dark theme */
select.form-input {
    background: rgba(0,0,0,0.35);
    color: #fff;
    appearance: none;
    cursor: pointer;
}
select.form-input option {
    background: #1a1a1a;
    color: white;
}

/* BUTTON */
.submit-btn {
    width: 100%;
    padding: 14px;
    border-radius: 8px;
    border: none;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    background: rgba(0,200,255,0.45);
}
.submit-btn:hover {
    background: rgba(0,200,255,0.65);
}

/* ALERTS */
.success {
    background: rgba(46,204,113,0.28);
    padding: 12px;
    border-radius: 8px;
    font-weight: bold;
    margin-bottom: 15px;
}
.error {
    background: rgba(231,76,60,0.28);
    padding: 12px;
    border-radius: 8px;
    font-weight: bold;
    margin-bottom: 15px;
}

</style>

</head>
<body>

<div class="patient-panel">

<?php include "sidebar.php"; ?>

<div class="patient-content">

<h1>📨 Mail Your Doctor</h1>

<div class="mail-container">
<div class="mail-card">

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <h2>Send Message</h2>

        <label>Select Doctor</label>
        <select name="doctor_id" class="form-input" required>
            <option value="">Choose doctor...</option>

            <?php foreach ($doctors as $d): ?>
                <option value="<?php echo $d['id']; ?>">
                    Dr. <?php echo $d['name']; ?> 
                    <?php if($d['specialization']) echo " — ".$d['specialization']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Subject</label>
        <input type="text" name="subject" class="form-input" placeholder="Enter subject" required>

        <label>Message</label>
        <textarea name="message" class="form-input" placeholder="Write your message..." required></textarea>

        <label>Attachment (optional)</label>
        <input type="file" name="attachment" class="form-input">

        <button class="submit-btn" name="send_mail">Send Mail</button>

    </form>

</div>
</div>

</div>
</div>

</body>
</html>
