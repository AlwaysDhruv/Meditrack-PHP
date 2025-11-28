<?php
require "../auth/patient_guard.php";
require "../config/db.php";

// Fetch all doctors
$stmt = $pdo->query("SELECT * FROM users WHERE role='doctor'");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Find Doctors | MediTrack</title>
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .doc-card {
            padding: 22px;
            border-radius: 12px;
            background: rgba(255,255,255,0.10);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .doc-info {
            margin-bottom: 12px;
        }
        .btn-book {
            display: block;
            text-align: center;
            padding: 10px;
            background: rgba(0,180,255,0.25);
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-book:hover {
            background: rgba(0,180,255,0.45);
        }
    </style>
</head>

<body>

<div class="patient-panel">

    <?php include "sidebar.php"; ?>

    <div class="patient-content">
        <h1>🧑‍⚕️ Find Doctors</h1>

        <div class="dashboard-grid">

        <?php foreach ($doctors as $doc): ?>
            <div class="doc-card">

                <h3>Dr. <?php echo htmlspecialchars($doc['name']); ?></h3>

                <p class="doc-info">
                    <strong>Specialization:</strong><br>
                    <?php echo $doc['specialization'] ?: "General Specialist"; ?>
                </p>

                <a href="patient_book.php?doctor=<?php echo $doc['id']; ?>" 
                   class="btn-book">
                    ➕ Book Appointment
                </a>

            </div>
        <?php endforeach; ?>

        </div>
    </div>

</div>

</body>
</html>
