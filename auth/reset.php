<?php
session_start();
require "../config/db.php";

// If user didn't complete OTP verification
if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password = trim($_POST['password'] ?? "");

    if ($password === "") {
        $message = "Please enter a new password.";
    } else {

        $email = $_SESSION['otp_email'];
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        // Update password using PDO
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        
        if ($stmt->execute([$newHash, $email])) {

            // Cleanup OTP session
            unset($_SESSION['otp']);
            unset($_SESSION['otp_email']);

            header("Location: login.php?reset=success");
            exit();

        } else {
            $message = "Something went wrong while updating your password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-box">
        <h2>Reset Password</h2>

        <?php if ($message): ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input 
                type="password" 
                name="password" 
                placeholder="Enter new password" 
                required
            >

            <button type="submit">Save Password</button>
        </form>
    </div>
</div>

</body>
</html>
