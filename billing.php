<?php
include('db_config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit;
}

$customerID = $_SESSION['user_id'];

// fetch customer details
$sql_cust="SELECT FullName, Email ,PhoneNumber, Address FROM Customers WHERE CustomerID = ?";
$stmt_cust=$conn->prepare($sql_cust);
$stmt_cust->bind_param("i",$customerID);
$stmt_cust->execute();
$result_cust=$stmt_cust->get_result();
$custinfo=$result_cust->fetch_assoc();


$stmt_cust->close();

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


// Fetch customer's plan details
$sql_plan = "SELECT p.PlanID,p.PlanName,p.MonthlyCost
    FROM CustomerPlans cp
    JOIN Plans p ON cp.PlanID = p.PlanID
    WHERE cp.CustomerID = ?";
$stmt_plan = $conn->prepare($sql_plan);
$stmt_plan->bind_param('i', $customerID);
$stmt_plan->execute();
$result_plan = $stmt_plan->get_result();
// Check if the customer has a plan
if ($result_plan->num_rows > 0) {
    $plan = $result_plan->fetch_assoc();
} else {
    $plan = null; // No plan assigned
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
    <link rel="stylesheet" href="static/css/billing.css">
</head>
<body>
    <?php include("header.html"); ?>
    <div class="bill-body">
    <h2>Your Billing Summary</h2>

        <div class="bill-container">
            <div class="bill-header">
                <div class="logo-bill"><img src="https://rilstaticasset.akamaized.net/sites/default/files/2024-03/JPEG_Download_Jio-Logo-Colour-Red.jpg" height="100%" width="100%" alt="logo" ></div>
                <div class="name-section">
                    <h4>Name:<? echo($custinfo["FullName"])?></h4><br>
                    <h5>Email:<? echo($custinfo["Email"])?></h5><br>
                    <h5>Phone Number:+91<? echo($custinfo["PhoneNumber"])?></h5><br>
                  <h5>  Address:<? echo($custinfo["Address"])?></h5>
                </div>
            </div>
          
            <div class="bill-content">
                
            <?php if ($zero_bill && $_SESSION['plan_flag']==false): ?>
        <p>Your latest bill is ₹0. Usage details are reset.</p>
        <table >
            <tr><th>Used Minutes</th><td>0 mins</td></tr>
            <tr><th>Used SMS</th><td>0 SMS</td></tr>
            <tr><th>Used Data</th><td>0 GB</td></tr>
            <tr><th>Total Bill</th><td>&#x20B9; 0.00</td></tr>
        </table>
    <?php elseif (isset($bill) && $_SESSION['plan_flag']==false): ?>
       
        <table >
            <tr><th>Used Minutes</th><td><?= htmlspecialchars($bill['TotalMinutes']) ?> mins</td></tr>
            <tr><th>Used SMS</th><td><?= htmlspecialchars($bill['TotalSMS']) ?> SMS</td></tr>
            <tr><th>Used Data</th><td><?= htmlspecialchars($bill['TotalData']) ?> GB</td></tr>
            <tr><th>Total Bill</th><td>&#x20B9; <?= number_format($bill['TotalBill'], 2); ?></td></tr>
        </table>
    <?php elseif($_SESSION['plan_flag']==True):?> 
        <table>
            <tr><th>Plan Name:</th><td><?echo $plan["PlanName"]?></td></tr>
            <tr><th>Amount Paid</th><td><?echo $plan["MonthlyCost"]?></td></tr>
        </table>   
    <?php else: ?>
        <p>No usage records found.</p>
    <?php endif; ?>
            </div>
            <div class="bill-footer">
                <img src="https://stampvala.com/wp-content/uploads/2022/05/Round-Stamp-07.webp" height="100em" width="100em" alt="stamp">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRfjQJq51Lk7mSuB9RpsA8OuE7QixuVFnejmg&s" height="80em" width="200em" alt="signiture"> 

            </div>
        </div>

    </div>

</body>
</html>
