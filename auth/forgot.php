<?php
session_start();
require "../config/db.php";
require "mailer.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? "");

    if ($email === "") {
        $message = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email.";
    } else {

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            // Generate OTP
            $otp = random_int(100000, 999999);

            $_SESSION['otp_email'] = $email;
            $_SESSION['otp'] = $otp;

            // Send email
            sendMail(
                $email,
                "Your OTP Code",
                "Your OTP for password reset is: <b>$otp</b><br>Do not share it with anyone."
            );

            header("Location: verify.php");
            exit();
        } else {
            $message = "Email not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
  <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-container">
<div class="auth-box">
  <h2>Forgot Password</h2>

  <?php if ($message): ?>
      <div class="error"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <form method="POST">
    <input type="email" name="email" placeholder="Enter your registered email" required>
    <button type="submit">Send OTP</button>
  </form>

  <div class="auth-extra">
      <a href="login.php">Back to Login</a>
  </div>
</div>
</div>

</body>
</html>
