<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

echo "Welcome, Admin " . htmlspecialchars($_SESSION['email']) . "!";
?>

<a href="logout.php">Logout</a>
