<?php
session_start();

/* ============================================================
   1. Disable Browser Cache (Prevents Back Button Access)
   ============================================================ */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

/* ============================================================
   2. Destroy Session
   ============================================================ */
session_unset();
session_destroy();

/* ============================================================
   3. Redirect to Login Page
   ============================================================ */
header("Location: /meditrack/auth/login.php");
exit();
?>
