<?php
require "../auth/patient_guard.php";
require "../config/db.php";

// Fetch appointments for patient
$stmt = $pdo->prepare("
    SELECT a.*, u.name AS doctor_name 
    FROM appointments a
    JOIN users u ON a.doctor_id = u.id
    WHERE a.patient_id = ?
    ORDER BY a.date DESC
");
$stmt->execute([$patient['id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Badge function
function statusBadge($status) {
    $s = strtolower(trim($status));

    return match ($s) {
        "requested" => "<span class='badge badge-requested'>Requested</span>",
        "accepted"  => "<span class='badge badge-accepted'>Accepted</span>",
        "rejected"  => "<span class='badge badge-rejected'>Rejected</span>",
        "completed" => "<span class='badge badge-completed'>Completed</span>",
        default     => $status
    };
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Appointments | MediTrack</title>
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .appointment-card {
            padding: 22px;
            border-radius: 12px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.35);
        }

        /* Header layout */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }
        .badge-requested { background:#f1c40f; color:black; }
        .badge-accepted { background:#2ecc71; }
        .badge-rejected { background:#e74c3c; }
        .badge-completed { background:#3498db; }
    </style>

</head>
<body>

<div class="patient-panel">

    <?php include "sidebar.php"; ?>

    <div class="patient-content">
        <h1>📅 My Appointments</h1>

        <div class="dashboard-grid">

        <?php foreach ($appointments as $a): ?>
            <div class="appointment-card">

                <!-- HEADER -->
                <div class="card-header">
                    <h3>Dr. <?php echo htmlspecialchars($a['doctor_name']); ?></h3>
                    <?php echo statusBadge($a['status']); ?>
                </div>

                <p><strong>Date:</strong><br>
                    <?php echo date("d M Y, h:i A", strtotime($a['date'])); ?>
                </p>

                <p><strong>Reason:</strong><br>
                    <?php echo nl2br(htmlspecialchars($a['reason'])); ?>
                </p>

                <?php if ($a['status'] == 'accepted'): ?>
                    <p style="margin-top:10px;">
                        ✔ Your appointment has been accepted.
                    </p>
                <?php endif; ?>

                <?php if ($a['status'] == 'completed'): ?>
                    <p style="margin-top:10px;">
                        📄 This appointment is marked as completed.
                    </p>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

        </div>
    </div>

</div>

</body>
</html>
