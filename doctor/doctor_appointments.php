<?php
require "../auth/doctor_guard.php";
require "../config/db.php";

$stmt = $pdo->prepare("
    SELECT a.*, u.name AS patient_name 
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id=?
    ORDER BY a.date ASC
");
$stmt->execute([$doctor['id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// STATUS BADGES
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
    <title>Doctor Appointments</title>
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

        /* HEADER ROW WITH BADGE ON RIGHT */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        /* BADGES */
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
        }
        .badge-requested { background:#f1c40f; color:black; }
        .badge-accepted { background:#2ecc71; }
        .badge-rejected { background:#e74c3c; }
        .badge-completed { background:#3498db; }

        /* BUTTONS */
        .btn-action {
            display: block;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            margin-top: 12px;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-accept { background: rgba(46,204,113,0.35); }
        .btn-reject { background: rgba(231,76,60,0.35); }
        .btn-complete { background: rgba(52,152,219,0.35); }
        .btn-accept:hover { background: rgba(46,204,113,0.55); }
        .btn-reject:hover { background: rgba(231,76,60,0.55); }
        .btn-complete:hover { background: rgba(52,152,219,0.55); }
    </style>

</head>
<body>

<div class="patient-panel">

    <?php include "sidebar_doctor.php"; ?>

    <div class="patient-content">
        <h1>📅 Appointments</h1>

        <div class="dashboard-grid">

        <?php foreach ($appointments as $a):
            $status = strtolower(trim($a['status']));
        ?>
            <div class="appointment-card">

                <!-- NEW CLEAN HEADER ROW -->
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($a['patient_name']); ?></h3>
                    <?php echo statusBadge($a['status']); ?>
                </div>

                <p><strong>Date:</strong><br>
                    <?php echo date("d M Y, h:i A", strtotime($a['date'])); ?>
                </p>

                <p><strong>Reason:</strong><br>
                    <?php echo nl2br(htmlspecialchars($a['reason'])); ?>
                </p>

                <!-- BUTTONS -->
                <?php if ($status == "requested"): ?>
                    <a href="appointment_action.php?id=<?php echo $a['id']; ?>&action=accept" 
                       class="btn-action btn-accept">✔ Accept</a>
                    <a href="appointment_action.php?id=<?php echo $a['id']; ?>&action=reject" 
                       class="btn-action btn-reject">❌ Reject</a>
                <?php endif; ?>

                <?php if ($status == "accepted"): ?>
                    <a href="appointment_action.php?id=<?php echo $a['id']; ?>&action=complete" 
                       class="btn-action btn-complete">✔ Mark as Completed</a>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

        </div>
    </div>

</div>

</body>
</html>
