<?php
require "../auth/doctor_guard.php";
require "../config/db.php";

// Count appointments
$stmt1 = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id=?");
$stmt1->execute([$doctor['id']]);
$totalAppointments = $stmt1->fetchColumn();

// Count patients (unique)
$stmt2 = $pdo->prepare("
    SELECT COUNT(DISTINCT patient_id) 
    FROM appointments 
    WHERE doctor_id=?
");
$stmt2->execute([$doctor['id']]);
$totalPatients = $stmt2->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="patient-panel">

    <?php include "sidebar_doctor.php"; ?>

    <div class="patient-content">
        <h1>Hello Dr. <?php echo htmlspecialchars($doctor['name']); ?> 👋</h1>

        <div class="dashboard-grid">

            <div class="card">
                <h3>📅 Total Appointments</h3>
                <h2><?php echo $totalAppointments; ?></h2>
            </div>

            <div class="card">
                <h3>🧑‍🤝‍🧑 Patients Seen</h3>
                <h2><?php echo $totalPatients; ?></h2>
            </div>

        </div>
    </div>

</div>

</body>
</html>
