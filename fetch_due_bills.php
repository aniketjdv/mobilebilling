<?php
include('db_config.php');
session_start();

$customerID = $_SESSION['user_id'];

$sql = "SELECT BillID, AmountDue, DueDate FROM Billing WHERE CustomerID = ? AND Status = 'Pending' ORDER BY BillId DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();

$bill = $result->fetch_assoc();
echo json_encode($bill);
?>
