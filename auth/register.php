<?php
require "../config/db.php";
$message = "";

/* ============================
   USER REGISTRATION
============================ */
if (isset($_POST['register'])) {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role  = $_POST['role'];
    $spec  = ($role === "doctor") ? trim($_POST['specialization']) : null;

    // Check email
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $message = "Email already registered!";
    } else {
        $sql = $pdo->prepare("
            INSERT INTO users (name, email, password, role, specialization)
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($sql->execute([$name, $email, $pass, $role, $spec])) {
            header("Location: login.php");
            exit();
        } else {
            $message = "Registration failed!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Register | MediTrack</title>

<link rel="stylesheet" href="../assets/css/auth.css">

<style>

/* Centering */
.auth-container {
  display:flex;
  justify-content:center;
  align-items:center;
  height:100vh;
  background: linear-gradient(135deg, #001a14, #003020);
}

/* Card */
.auth-box {
  width: 400px;
  padding: 30px;
  background: rgba(255,255,255,0.10);
  backdrop-filter: blur(12px);
  border-radius: 14px;
  box-shadow: 0 10px 35px rgba(0,0,0,0.4);
  color: white;
  text-align:center;
}

.auth-box h2 {
  margin-bottom: 20px;
}

/* Inputs */
.auth-box input, 
.auth-box select {
  width: 100%;
  padding: 14px;
  margin-bottom: 15px;
  border-radius: 8px;
  border: none;
  background: rgba(255,255,255,0.15);
  color: white;
}
.auth-box input::placeholder {
  color: #e0e0e0;
}

.auth-box select option {
  background: #00241b;
  color: white;
}

/* Button */
.auth-box button {
  width: 100%;
  padding: 14px;
  border-radius: 8px;
  border: none;
  background: #00c4b4;
  cursor:pointer;
  font-size:16px;
  font-weight:bold;
}
.auth-box button:hover {
  background: #00e0cc;
}

/* Error */
.error {
  background:rgba(255,80,80,0.3);
  padding:10px;
  border-radius:8px;
  margin-bottom:10px;
  font-weight:bold;
  color:white;
}

/* Extra links */
.auth-extra {
  margin-top:15px;
}
.auth-extra a {
  color:#00ffd4;
}

</style>

<script>
// Show specialization only if doctor is selected
function toggleSpec() {
    let role = document.getElementById("role").value;
    let specField = document.getElementById("spec-field");
    specField.style.display = (role === "doctor") ? "block" : "none";
}
</script>

</head>
<body>

<div class="auth-container">
<div class="auth-box">

  <h2>Create Account</h2>

  <?php if ($message): ?>
    <p class="error"><?= $message ?></p>
  <?php endif; ?>

  <form method="POST">

    <input type="text" name="name" placeholder="Full Name" required>

    <input type="email" name="email" placeholder="Email Address" required>

    <input type="password" name="password" placeholder="Password" required>

    <select name="role" id="role" onchange="toggleSpec()" required>
      <option value="patient">Patient</option>
      <option value="doctor">Doctor</option>
    </select>

    <input type="text" 
           id="spec-field" 
           name="specialization" 
           placeholder="Specialization (Doctor only)"
           style="display:none;">

    <button name="register">Register</button>

    <div class="auth-extra">
      <a href="login.php">Already have an account? Login</a>
    </div>

  </form>

</div>
</div>

</body>
</html>
