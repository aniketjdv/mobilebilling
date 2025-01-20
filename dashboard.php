<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

echo "Welcome, " . htmlspecialchars($_SESSION['email']) . "!";
?>

<a href="logout.php">Logout</a>
