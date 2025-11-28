<?php
session_start();
$message = "";

if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot.php");
    exit();
}

if (isset($_POST['verify'])) {
    if ($_POST['otp'] == $_SESSION['otp']) {
        header("Location: reset.php");
        exit();
    } else {
        $message = "Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Verify OTP</title>
  <link rel="stylesheet" href="../assets/css/auth.css"> 
</head>
<body>

<div class="auth-container">
<div class="auth-box">
  <h2>Verify OTP</h2>

  <?php if ($message) echo "<p class='error'>$message</p>"; ?>

  <form method="POST">
    <input type="number" name="otp" placeholder="Enter OTP" required>
    <button name="verify">Verify</button>
  </form>
</div>
</div>

</body>
</html>
