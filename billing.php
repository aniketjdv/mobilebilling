<?php
include('db_config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit;
}

$customerID = $_SESSION['user_id'];

// SQL query to calculate total bill
$sql = "
    SELECT 
        CustomerID, 
        SUM(UsedMinutes) AS TotalMinutes, 
        SUM(UsedSMS) AS TotalSMS, 
        SUM(UsedData) AS TotalData, 
        (SUM(UsedMinutes) * 1 + SUM(UsedSMS) * 0.5 + SUM(UsedData) * 10) AS TotalBill 
    FROM Cust_Usage
    WHERE CustomerID = ?
    GROUP BY CustomerID;
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
$bill = $result->fetch_assoc();

// Close connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Summary</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <h2>Your Billing Summary</h2>
    
    <?php if ($bill): ?>
        <table>
            <tr>
                <th>Used Minutes</th>
                <td><?= htmlspecialchars($bill['TotalMinutes']) ?> mins</td>
            </tr>
            <tr>
                <th>Used SMS</th>
                <td><?= htmlspecialchars($bill['TotalSMS']) ?> SMS</td>
            </tr>
            <tr>
                <th>Used Data</th>
                <td><?= htmlspecialchars($bill['TotalData']) ?> GB</td>
            </tr>
            <tr>
                <th>Total Bill</th>
                <td>&#x20B9; <?= number_format($bill['TotalBill'], 2) ?></td>
            </tr>
        </table>
    <?php else: ?>
        <p>No usage records found.</p>
    <?php endif; ?>
</body>
</html>