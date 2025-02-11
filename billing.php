<?php
include('db_config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit;
}

$customerID = $_SESSION['user_id'];

// Fetch the latest bill
$sql_bill = "SELECT AmountDue FROM Billing WHERE CustomerID = ? ORDER BY BillId DESC LIMIT 1";
$stmt_bill = $conn->prepare($sql_bill);
$stmt_bill->bind_param("i", $customerID);
$stmt_bill->execute();
$result_bill = $stmt_bill->get_result();
$latest_bill = $result_bill->fetch_assoc();
$stmt_bill->close();

$zero_bill = ($latest_bill && $latest_bill['AmountDue'] == 0);
echo $zero_bill;
// SQL query to calculate total bill if the latest bill is not zero
if (!$zero_bill) {
    $sql_usage = "
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
    $stmt_usage = $conn->prepare($sql_usage);
    $stmt_usage->bind_param("i", $customerID);
    $stmt_usage->execute();
    $result_usage = $stmt_usage->get_result();
    $bill = $result_usage->fetch_assoc();
    $stmt_usage->close();
}

// Close connection
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
    <?php include("header.html"); ?>
    
    <h2>Your Billing Summary</h2>

    <?php if ($zero_bill): ?>
        <p>Your latest bill is ₹0. Usage details are reset.</p>
        <table>
            <tr><th>Used Minutes</th><td>0 mins</td></tr>
            <tr><th>Used SMS</th><td>0 SMS</td></tr>
            <tr><th>Used Data</th><td>0 GB</td></tr>
            <tr><th>Total Bill</th><td>&#x20B9; 0.00</td></tr>
        </table>
    <?php elseif ($bill): ?>
        <table>
            <tr><th>Used Minutes</th><td><?= htmlspecialchars($bill['TotalMinutes']) ?> mins</td></tr>
            <tr><th>Used SMS</th><td><?= htmlspecialchars($bill['TotalSMS']) ?> SMS</td></tr>
            <tr><th>Used Data</th><td><?= htmlspecialchars($bill['TotalData']) ?> GB</td></tr>
            <tr><th>Total Bill</th><td>&#x20B9; <?= number_format($bill['TotalBill'], 2); ?></td></tr>
        </table>
    <?php else: ?>
        <p>No usage records found.</p>
    <?php endif; ?>

</body>
</html>
