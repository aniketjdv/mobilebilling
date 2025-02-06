<?php
session_start();
include('db_config.php');

if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit;
}

$customerID = $_SESSION['user_id'];

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'];
    $stmt = $conn->prepare("INSERT INTO SupportMessages (CustomerID, Message) VALUES (?, ?)");
    $stmt->bind_param('is', $customerID, $message);
    $stmt->execute();
    header("Location: customer_support.php");
}

// Fetch Customer's Messages
$result = $conn->query("SELECT * FROM SupportMessages WHERE CustomerID = $customerID ORDER BY Timestamp DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Support</title>
    <link rel="stylesheet" href="static/css/support.css">
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <?include "header.html"?>
    <h2>Contact Support</h2>
    <form method="POST">
        <textarea name="message" placeholder="Describe your issue..." required></textarea>
        <button type="submit">Send</button>
    </form>

    <h3>Your Support Messages</h3>
    <table>
        <tr><th>Message</th><th>Response</th><th>Status</th></tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['Message']) ?></td>
            <td><?= $row['Response'] ? htmlspecialchars($row['Response']) : "No response yet" ?></td>
            <td><?= $row['Status'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
