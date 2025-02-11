<?php
include('db_config.php');
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$customerID = $_SESSION['user_id'];

// Fetch the latest bill
$sql_bill = "SELECT BillID, AmountDue FROM Billing WHERE CustomerID = ? ORDER BY BillId DESC LIMIT 1";
$stmt_bill = $conn->prepare($sql_bill);
$stmt_bill->bind_param("i", $customerID);
$stmt_bill->execute();
$result_bill = $stmt_bill->get_result();
$latest_bill = $result_bill->fetch_assoc();
$stmt_bill->close();

if (!$latest_bill || $latest_bill['AmountDue'] == 0) {
    echo "No pending bill to pay.";
    exit;
}

$billID = $latest_bill['BillID'];
$amountDue = $latest_bill['AmountDue'];
$paymentMethod = $_POST['paymentMethod']; // 'Credit', 'Debit', or 'UPI'

// Fetch total usage before resetting
$sql_usage = "SELECT SUM(UsedMinutes) AS TotalMinutes, SUM(UsedSMS) AS TotalSMS, SUM(UsedData) AS TotalData 
              FROM Cust_Usage WHERE CustomerID = ?";
$stmt_usage = $conn->prepare($sql_usage);
$stmt_usage->bind_param("i", $customerID);
$stmt_usage->execute();
$result_usage = $stmt_usage->get_result();
$usage = $result_usage->fetch_assoc();
$stmt_usage->close();

// Start transaction
$conn->begin_transaction();

try {
    // Insert payment record
    $sql_payment = "INSERT INTO Payments (BillID, CustomerID, AmountPaid, PaymentDate, PaymentMethod, UsedMinutes, UsedSMS, UsedData) 
                    VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)";
    $stmt_payment = $conn->prepare($sql_payment);
    $stmt_payment->bind_param("iidssdd", $billID, $customerID, $amountDue, $paymentMethod, 
                              $usage['TotalMinutes'], $usage['TotalSMS'], $usage['TotalData']);
   if($stmt_payment->execute()==True){
    echo "payment update";
   }
    $stmt_payment->close();

    // Update Billing table: Set AmountDue to 0
    $sql_update_bill = "UPDATE Billing SET AmountDue = 0 WHERE BillID = ?";
    $stmt_update_bill = $conn->prepare($sql_update_bill);
    $stmt_update_bill->bind_param("i", $billID);
    $stmt_update_bill->execute();
    $stmt_update_bill->close();

    // Insert a new Cust_Usage record with 0 values
    $sql_reset_usage = "INSERT INTO Cust_Usage (CustomerID, UsedMinutes, UsedSMS, UsedData, UsageDate) 
                        VALUES (?, 0, 0, 0, NOW())";
    $stmt_reset_usage = $conn->prepare($sql_reset_usage);
    $stmt_reset_usage->bind_param("i", $customerID);
    $stmt_reset_usage->execute();
    $stmt_reset_usage->close();

    // Commit transaction
    $conn->commit();

    echo "Payment successful! Usage has been reset.";
} catch (Exception $e) {
    $conn->rollback();
    echo "Payment failed: " . $e->getMessage();
}

$conn->close();
?>
