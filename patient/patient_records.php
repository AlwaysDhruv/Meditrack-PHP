<?php
require "../auth/patient_guard.php";
require "../config/db.php";

// Fetch all health records for this patient
$stmt = $pdo->prepare("
    SELECT r.*, u.name AS doctor_name
    FROM records r
    JOIN users u ON r.doctor_id = u.id
    WHERE r.patient_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$patient['id']]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Health Records | MediTrack</title>
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .record-card {
            padding: 22px;
            border-radius: 12px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.32);
            margin-bottom: 12px;
        }

        .rec-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .rec-badge {
            background: #3498db;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: bold;
            color: white;
            font-size: 13px;
        }

        .vital-box {
            margin: 8px 0;
            padding: 8px;
            background: rgba(255,255,255,0.09);
            border-radius: 8px;
        }

        .vital-box strong {
            display: inline-block;
            width: 110px;
        }
    </style>
</head>

<body>

<div class="patient-panel">

    <?php include "sidebar.php"; ?>

    <div class="patient-content">

        <h1>🩺 My Health Records</h1>

        <div class="dashboard-grid">

            <?php foreach ($records as $rec): ?>
                <div class="record-card">

                    <div class="rec-header">
                        <h3><?php echo htmlspecialchars($rec['title'] ?: "Health Record"); ?></h3>
                        <span class="rec-badge">
                            <?php echo date("d M Y", strtotime($rec["created_at"])); ?>
                        </span>
                    </div>

                    <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($rec['doctor_name']); ?></p>

                    <?php if ($rec['description']): ?>
                        <p><strong>Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($rec['description'])); ?></p>
                    <?php endif; ?>

                    <div class="vital-box">
                        <?php if ($rec['bp']): ?>
                            <p><strong>Blood Pressure:</strong> <?php echo $rec['bp']; ?></p>
                        <?php endif; ?>

                        <?php if ($rec['pulse']): ?>
                            <p><strong>Pulse:</strong> <?php echo $rec['pulse']; ?></p>
                        <?php endif; ?>

                        <?php if ($rec['temperature']): ?>
                            <p><strong>Temperature:</strong> <?php echo $rec['temperature']; ?></p>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

    </div>

</div>

</body>
</html>
