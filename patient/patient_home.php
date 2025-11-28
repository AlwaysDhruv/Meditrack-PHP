<?php
require "../auth/patient_guard.php";
require "../config/db.php";

/* ==========================
   FETCH DATA FOR DASHBOARD
========================== */

// Fetch upcoming appointments
$astmt = $pdo->prepare("
    SELECT a.*, u.name AS doctor_name 
    FROM appointments a
    JOIN users u ON a.doctor_id = u.id
    WHERE a.patient_id = ?
    ORDER BY a.date ASC
    LIMIT 5
");
$astmt->execute([$patient["id"]]);
$appointments = $astmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent records
$rstmt = $pdo->prepare("
    SELECT * FROM records 
    WHERE patient_id = ?
    ORDER BY created_at DESC 
    LIMIT 3
");
$rstmt->execute([$patient["id"]]);
$records = $rstmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard | MediTrack</title>

<link rel="stylesheet" href="../assets/css/panel.css">
<link rel="stylesheet" href="../assets/css/style.css">

<style>

/* PAGE */
.dashboard-page {
    padding: 20px;
}

/* TITLE SECTION */
.dash-title {
    font-size: 32px;
    font-weight: bold;
}
.dash-subtitle {
    opacity: .8;
    margin-bottom: 25px;
}

/* STATS */
.stats-row {
    display: flex;
    gap: 20px;
}
.stat-card {
    flex: 1;
    background: rgba(255,255,255,0.12);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 6px 25px rgba(0,0,0,0.3);
}
.stat-card h3 {
    font-size: 32px;
}
.stat-card p {
    margin-top: 6px;
    opacity: 0.85;
}

/* QUICK ACTIONS */
.quick-actions {
    margin-top: 35px;
}
.actions-row {
    display: flex;
    gap: 20px;
}
.action-card {
    flex: 1;
    padding: 22px;
    border-radius: 12px;
    text-decoration: none;
    background: rgba(255,255,255,0.10);
    color: white;
    text-align: center;
    box-shadow: 0 6px 25px rgba(0,0,0,0.35);
}
.action-card:hover {
    background: rgba(255,255,255,0.18);
}
.action-card .icon {
    font-size: 38px;
    margin-bottom: 8px;
}

/* SECTIONS */
.section-block {
    margin-top: 40px;
    background: rgba(255,255,255,0.10);
    padding: 22px;
    border-radius: 12px;
}
.list-card {
    background: rgba(255,255,255,0.15);
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 12px;
}
.empty {
    opacity: .6;
    font-style: italic;
}
.view-more {
    display: inline-block;
    margin-top: 10px;
    opacity: .9;
}

</style>
</head>

<body>

<div class="patient-panel">

<?php include "sidebar.php"; ?>

<div class="patient-content">

<div class="dashboard-page">

    <!-- TITLE -->
    <h1 class="dash-title">Welcome, <?php echo htmlspecialchars($patient["name"]); ?></h1>
    <p class="dash-subtitle">Your health at a glance</p>

    <!-- STATS -->
    <div class="stats-row">
        
        <div class="stat-card">
            <h3><?php echo count($appointments); ?></h3>
            <p>Upcoming Appointments</p>
        </div>

        <div class="stat-card">
            <h3><?php echo count($records); ?></h3>
            <p>Health Records</p>
        </div>

        <div class="stat-card">
            <h3>🤖</h3>
            <p>AI Assistant</p>
        </div>

    </div>

    <!-- QUICK ACTIONS -->
    <div class="quick-actions">
        <h2>Quick Actions</h2>

        <div class="actions-row">

            <a href="patient_appointments.php" class="action-card">
                <div class="icon">📅</div>
                <h3>Book Appointment</h3>
                <p>Find doctors & schedule visits</p>
            </a>

            <a href="patient_records.php" class="action-card">
                <div class="icon">📄</div>
                <h3>Your Records</h3>
                <p>View & manage health records</p>
            </a>

            <a href="patient_chat_ai.php" class="action-card">
                <div class="icon">🤖</div>
                <h3>AI Health Chat</h3>
                <p>Get AI-powered guidance</p>
            </a>

        </div>
    </div>

    <!-- UPCOMING APPOINTMENTS -->
    <div class="section-block">
        <h2>Upcoming Appointments</h2>

        <?php if (count($appointments) == 0): ?>
            <p class="empty">No upcoming appointments</p>
        <?php endif; ?>

        <?php foreach ($appointments as $app): ?>
            <div class="list-card">
                <h3><?php echo date("d M Y, h:i A", strtotime($app["date"])); ?></h3>
                <p><strong>Doctor:</strong> Dr. <?php echo $app["doctor_name"]; ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($app["status"]); ?></p>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- RECENT RECORDS -->
    <div class="section-block">
        <h2>Recent Health Records</h2>

        <?php if (count($records) == 0): ?>
            <p class="empty">No records added yet</p>
        <?php endif; ?>

        <?php foreach ($records as $rec): ?>
            <div class="list-card">
                <h3><?php echo date("d M Y", strtotime($rec["created_at"])); ?></h3>
                <p><strong>Title:</strong> <?php echo htmlspecialchars($rec["title"]); ?></p>
            </div>
        <?php endforeach; ?>

        <a href="patient_records.php" class="view-more">View All Records →</a>
    </div>

</div>

</div>
</div>

</body>
</html>
