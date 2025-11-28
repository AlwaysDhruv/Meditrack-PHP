<?php
session_start();
require "../config/db.php";   // uses PDO

// If already logged in, redirect to home router
if (isset($_SESSION["user"])) {
    header("Location: ../home.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $message = "Please enter email and password.";
    } else {

        // Fetch user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {

            // Save user in session
            $_SESSION["user"] = [
                "id"    => $user["id"],
                "name"  => $user["name"],
                "email" => $user["email"],
                "role"  => $user["role"]
            ];

            // Redirect based on role
            if ($user["role"] === "patient") {
                header("Location: ../patient/patient_home.php");
                exit();
            }

            if ($user["role"] === "doctor") {
                header("Location: ../doctor/doctor_home.php");
                exit();
            }

        } else {
            $message = "Invalid email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | MediTrack</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-container">
<div class="auth-box">

    <h2>Login</h2>

    <?php if ($message): ?>
        <div class="error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST">

        <input 
            type="email"
            name="email"
            placeholder="Email"
            value="<?php echo isset($email) ? htmlspecialchars($email) : ""; ?>"
            required
        >

        <input 
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <button type="submit" name="login">Login</button>

        <div class="auth-extra">
            <a href="register.php">Create account</a><br>
            <a href="forgot.php">Forgot password?</a>
        </div>

    </form>

</div>
</div>

</body>
</html>
