<?php
include('db_config.php');
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$customerID = $_SESSION['user_id'];

// Simulate usage
$callMinutes = rand(50, 500);
$smsCount = rand(10, 100);
$dataUsed = rand(1, 10);
$date = date('Y-m-d');

$sql = "INSERT INTO Cust_Usage (CustomerID, UsedMinutes, UsedSMS, UsedData, Date) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiids', $customerID, $callMinutes, $smsCount, $dataUsed, $date);

if ($stmt->execute()) {
    header('Location: simulate_usage.php');
    exit();
} else {
    echo "Error simulating usage.";
}
?>
