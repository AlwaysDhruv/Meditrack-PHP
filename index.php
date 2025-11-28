<?php
session_start();

// If user already logged in → go to home redirect
if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MediTrack • Smart Health System</title>
    <link rel="stylesheet" href="/meditrack/assets/css/style.css">
</head>

<body>

<div class="home">

    <!-- NAVBAR -->
    <div class="nav">
        <div class="logo">MediTrack 🩺</div>
        <div>
            <a href="/meditrack/auth/login.php">Login</a>
            <a href="/meditrack/auth/register.php">Register</a>
        </div>
    </div>

    <!-- HERO SECTION -->
    <div class="hero">
        <h1>Your Health, Organized.</h1>
        <p>
            MediTrack helps patients book appointments, manage medical history,  
            view doctor records, and communicate seamlessly — all in one secure place.
        </p>

        <div class="hero-buttons">
            <a class="btn primary" href="/meditrack/auth/login.php">Login</a>
            <a class="btn secondary" href="/meditrack/auth/register.php">Create Account</a>
        </div>
    </div>

    <!-- FEATURES -->
    <div class="features">
        <div class="feature-card">
            <h3>Easy Appointments</h3>
            <p>Book appointments with qualified doctors in seconds.</p>
        </div>

        <div class="feature-card">
            <h3>Health Records</h3>
            <p>Your medical history stored safely and always accessible.</p>
        </div>

        <div class="feature-card">
            <h3>Smart Communication</h3>
            <p>Secure messaging between patients and doctors.</p>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        © <?php echo date("Y"); ?> MediTrack · Smart Health Record System
    </div>

</div>

</body>
</html>
