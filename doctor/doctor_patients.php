<?php
require "../auth/doctor_guard.php";
require "../config/db.php";
require "../auth/mailer.php";
require "../auth/pdf_helper.php";

/* ============================================
   1. INLINE ADD RECORD (EMAIL + PDF TO PATIENT)
=============================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_record"])) {

    $insert = $pdo->prepare("
        INSERT INTO records (patient_id, doctor_id, title, description, bp, pulse, temperature)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $insert->execute([
        $_POST["patient_id"],
        $doctor["id"],
        $_POST["title"],
        $_POST["description"],
        $_POST["bp"] ?: null,
        $_POST["pulse"] ?: null,
        $_POST["temperature"] ?: null
    ]);

    // Fetch patient email
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id=?");
    $stmt->execute([$_POST["patient_id"]]);
    $patient = $stmt->fetch();

    // Build PDF
    $record = [
        "title" => $_POST["title"],
        "description" => $_POST["description"],
        "bp" => $_POST["bp"],
        "pulse" => $_POST["pulse"],
        "temperature" => $_POST["temperature"]
    ];

    $pdfFile = generateRecordPDF($record, $doctor, $patient["email"]);

    // Email patient
    sendMail(
        $patient["email"],
        "New Medical Record Added",
        "<h3>A new medical record has been added by Dr. {$doctor['name']}.</h3>",
        $pdfFile
    );

    header("Location: doctor_patients.php?added=1");
    exit();
}


/* ============================================
   2. FETCH PATIENT LIST FOR DOCTOR
=============================================== */
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        u.id AS patient_id,
        u.name AS patient_name,
        u.email,
        (SELECT status FROM appointments 
         WHERE patient_id=u.id AND doctor_id=? 
         ORDER BY date DESC LIMIT 1) AS status,

        (SELECT date FROM appointments 
         WHERE patient_id=u.id AND doctor_id=? 
         ORDER BY date DESC LIMIT 1) AS last_visit
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = ?
    ORDER BY last_visit DESC
");
$stmt->execute([$doctor["id"], $doctor["id"], $doctor["id"]]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ============================================
   BADGE HELPER
=============================================== */
function badge($status){
    return match (strtolower(trim($status))) {
        "requested" => "<span class='badge badge-requested'>Requested</span>",
        "accepted" => "<span class='badge badge-accepted'>Accepted</span>",
        "rejected" => "<span class='badge badge-rejected'>Rejected</span>",
        "completed" => "<span class='badge badge-completed'>Completed</span>",
        default => "<span class='badge'>$status</span>"
    };
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Doctor Patients | MediTrack</title>

<link rel="stylesheet" href="../assets/css/panel.css">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
/* MAIN PATIENT CARD */
.patient-card {
    padding: 22px;
    background: rgba(255,255,255,0.12);
    border-radius: 12px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.3);
    margin-bottom: 14px;
}
.header-row {
    display: flex; justify-content: space-between; align-items: center;
}

/* BADGES */
.badge { padding: 6px 12px; border-radius: 6px; font-weight: bold; }
.badge-requested { background:#f1c40f; color:black; }
.badge-accepted { background:#2ecc71; }
.badge-rejected { background:#e74c3c; }
.badge-completed { background:#3498db; }

/* TOGGLE BUTTONS */
.toggle-btn {
    padding: 8px 12px;
    margin-top: 10px;
    display:inline-block;
    border-radius: 6px;
    cursor: pointer;
    background: rgba(0,200,255,0.3);
    font-weight: 600;
}
.toggle-btn:hover { background: rgba(0,200,255,0.5); }

/* INLINE SECTIONS */
.records-section, .add-record-section { display:none; margin-top:15px; }

/* RECORD BOX */
.record-box {
    padding:15px;
    background:rgba(255,255,255,0.08);
    border-radius:10px;
    margin-bottom:10px;
}
.record-header {
    display:flex; justify-content:space-between; align-items:center;
}

/* ACTION BUTTONS */
.btn-small {
    padding:6px 10px; border-radius:6px;
    cursor:pointer; font-size:13px; font-weight:bold;
}
.edit-btn { background:rgba(52,152,219,0.4); }
.edit-btn:hover { background:rgba(52,152,219,0.6); }
.del-btn { background:rgba(231,76,60,0.4); }
.del-btn:hover { background:rgba(231,76,60,0.6); }

/* INPUTS */
input, textarea {
    width:100%; padding:10px; border-radius:6px; margin-bottom:10px;
}
.submit-btn {
    width:100%; padding:10px; border-radius:8px; background:rgba(46,204,113,0.5);
}
.submit-btn:hover { background:rgba(46,204,113,0.7); }

/* SEARCH */
#searchPatient {
    width:100%; padding:12px; border-radius:8px; margin-bottom:18px;
}
.search-record-input {
    width:100%; padding:8px; border-radius:6px; margin-bottom:10px;
}
</style>

</head>

<body>

<div class="patient-panel">

<?php include "sidebar_doctor.php"; ?>

<div class="patient-content">

<h1>🧑‍⚕️ Patients</h1>

<!-- PATIENT SEARCH -->
<input type="text" id="searchPatient" placeholder="Search patient by name or email...">

<div id="patientList">

<?php foreach ($patients as $p): ?>

<div class="patient-card patient-item"
     data-name="<?php echo strtolower($p['patient_name']); ?>"
     data-email="<?php echo strtolower($p['email']); ?>">

    <div class="header-row">
        <h3><?php echo $p["patient_name"]; ?></h3>
        <?php echo badge($p["status"]); ?>
    </div>

    <p><strong>Email:</strong> <?php echo $p["email"]; ?></p>
    <p><strong>Last Visit:</strong> 
        <?php echo $p["last_visit"] ? date("d M Y, h:i A", strtotime($p["last_visit"])) : "No visit yet"; ?>
    </p>

    <!-- RECORD TOGGLES -->
    <div class="toggle-btn" onclick="toggle('records-<?php echo $p['patient_id']; ?>')">📄 View Records</div>
    <div class="toggle-btn" onclick="toggle('addrec-<?php echo $p['patient_id']; ?>')">➕ Add Record</div>

    <!-- INLINE ADD RECORD -->
    <div id="addrec-<?php echo $p['patient_id']; ?>" class="add-record-section">

        <form method="POST">
            <input type="hidden" name="add_record" value="1">
            <input type="hidden" name="patient_id" value="<?php echo $p['patient_id']; ?>">

            <label>Title</label>
            <input type="text" name="title" required>

            <label>Description</label>
            <textarea name="description" required></textarea>

            <label>BP</label><input type="text" name="bp">
            <label>Pulse</label><input type="text" name="pulse">
            <label>Temperature</label><input type="text" name="temperature">

            <button class="submit-btn">Save Record</button>
        </form>

    </div>

    <!-- INLINE RECORD LIST -->
    <div id="records-<?php echo $p['patient_id']; ?>" class="records-section">

        <!-- INLINE SEARCH RECORDS -->
        <input type="text"
               class="search-record-input"
               placeholder="Search records (title, description, vitals)..."
               onkeyup="filterRecords(this.value, <?php echo $p['patient_id']; ?>)">

        <?php
        $rec = $pdo->prepare("
            SELECT * FROM records 
            WHERE patient_id=? AND doctor_id=?
            ORDER BY created_at DESC
        ");
        $rec->execute([$p["patient_id"], $doctor["id"]]);
        $records = $rec->fetchAll(PDO::FETCH_ASSOC);

        if (!$records) echo "<p>No records available.</p>";

        foreach ($records as $r):
        ?>

        <!-- VIEW MODE -->
        <div class="record-box record-item-<?php echo $p['patient_id']; ?>"
             id="view-box-<?php echo $r['id']; ?>"
             data-search="<?php echo strtolower($r['title'].' '.$r['description'].' '.$r['bp'].' '.$r['pulse'].' '.$r['temperature']); ?>">

            <div class="record-header">
                <span><strong><?php echo $r["title"]; ?></strong></span>

                <div>
                    <span class="btn-small edit-btn" onclick="openEdit(<?php echo $r['id']; ?>)">✏ Edit</span>
                    <a class="btn-small del-btn"
                       href="doctor_record_delete.php?id=<?php echo $r['id']; ?>"
                       onclick="return confirm('Delete this record?')">🗑 Delete</a>
                </div>
            </div>

            <p><?php echo nl2br($r["description"]); ?></p>

            <ul>
                <?php if ($r["bp"]) echo "<li>BP: {$r['bp']}</li>"; ?>
                <?php if ($r["pulse"]) echo "<li>Pulse: {$r['pulse']}</li>"; ?>
                <?php if ($r["temperature"]) echo "<li>Temp: {$r['temperature']}</li>"; ?>
            </ul>

        </div>

        <!-- EDIT MODE -->
        <div class="record-box" id="edit-box-<?php echo $r['id']; ?>" style="display:none;">

            <form method="POST" action="doctor_record_update.php">
                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

                <label>Title</label>
                <input type="text" name="title" value="<?php echo $r['title']; ?>" required>

                <label>Description</label>
                <textarea name="description" required><?php echo $r['description']; ?></textarea>

                <label>BP</label><input type="text" name="bp" value="<?php echo $r['bp']; ?>">
                <label>Pulse</label><input type="text" name="pulse" value="<?php echo $r['pulse']; ?>">
                <label>Temperature</label><input type="text" name="temperature" value="<?php echo $r['temperature']; ?>">

                <button class="submit-btn">Save Changes</button>
                <div class="toggle-btn" onclick="closeEdit(<?php echo $r['id']; ?>)">Cancel</div>
            </form>

        </div>

        <?php endforeach; ?>
    </div>

</div>

<?php endforeach; ?>
</div>
</div>

<script>
// Patient search
document.getElementById("searchPatient").addEventListener("keyup", function(){
    let q = this.value.toLowerCase();
    document.querySelectorAll(".patient-item").forEach(p => {
        p.style.display = 
            p.dataset.name.includes(q) || p.dataset.email.includes(q)
            ? "block" : "none";
    });
});

// Toggle section
function toggle(id){
    let e = document.getElementById(id);
    e.style.display = (e.style.display === "block") ? "none" : "block";
}

// Inline edit
function openEdit(id){
    document.getElementById("view-box-" + id).style.display = "none";
    document.getElementById("edit-box-" + id).style.display = "block";
}
function closeEdit(id){
    document.getElementById("edit-box-" + id).style.display = "none";
    document.getElementById("view-box-" + id).style.display = "block";
}

// Inline record search
function filterRecords(q, pid){
    q = q.toLowerCase();
    document.querySelectorAll(".record-item-" + pid).forEach(r => {
        r.style.display = r.dataset.search.includes(q) ? "block" : "none";
    });
}
</script>

</body>
</html>
