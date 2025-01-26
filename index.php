
<?php
// Include the database configuration file
include('db_config.php');

session_start();
$_SESSION["user_id"]=1;
// Set the customer ID (for demonstration, this is static; in production, use session data or authentication)
$customerID = 1;

// Fetch customer details
$sql_customer = "SELECT FullName FROM Customers WHERE CustomerID = ?";
$stmt_customer = $conn->prepare($sql_customer);
$stmt_customer->bind_param('i', $customerID);
$stmt_customer->execute();
$result_customer = $stmt_customer->get_result();
$customer = $result_customer->fetch_assoc();
$name = $customer['FullName'] ?? 'Customer';

// Fetch current plan details
$sql_plan = "
    SELECT p.PlanName, p.MonthlyCost, cp.StartDate, cp.EndDate
    FROM CustomerPlans cp
    JOIN Plans p ON cp.PlanID = p.PlanID
    WHERE cp.CustomerID = ?
";
$stmt_plan = $conn->prepare($sql_plan);
$stmt_plan->bind_param('i', $customerID);
$stmt_plan->execute();
$result_plan = $stmt_plan->get_result();
$plan = $result_plan->fetch_assoc();
$planName = $plan['PlanName'] ?? 'N/A';
$monthlyCost = $plan['MonthlyCost'] ?? '0';
$renewalDate = $plan['EndDate'] ?? 'N/A';

// Fetch usage data
$sql_usage = "SELECT UsedMinutes, UsedSMS, UsedData FROM Cust_Usage WHERE CustomerID = ?";
$stmt_usage = $conn->prepare($sql_usage);
$stmt_usage->bind_param('i', $customerID);
$stmt_usage->execute();
$result_usage = $stmt_usage->get_result();
$usage = $result_usage->fetch_assoc();
$usedMinutes = $usage['UsedMinutes'] ?? 0;
$usedSMS = $usage['UsedSMS'] ?? 0;
$usedData = $usage['UsedData'] ?? 0;

// Fetch billing details
$sql_billing = "SELECT AmountDue, DueDate FROM Billing WHERE CustomerID = ? AND Status = 'Pending'";
$stmt_billing = $conn->prepare($sql_billing);
$stmt_billing->bind_param('i', $customerID);
$stmt_billing->execute();
$result_billing = $stmt_billing->get_result();
$billing = $result_billing->fetch_assoc();
$outstandingBalance = $billing['AmountDue'] ?? 0;
$dueDate = $billing['DueDate'] ?? 'N/A';

// Fetch notifications
$sql_notifications = "SELECT Message FROM Notifications WHERE CustomerID = ? AND IsRead = FALSE";
$stmt_notifications = $conn->prepare($sql_notifications);
$stmt_notifications->bind_param('i', $customerID);
$stmt_notifications->execute();
$result_notifications = $stmt_notifications->get_result();
$notifications = [];
while ($row = $result_notifications->fetch_assoc()) {
    $notifications[] = $row['Message'];
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Billing</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <?php
    include("header.html");
    ?>
    
    <div class="dashboard">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
            <p>Here's a quick overview of your account:</p>
        </div>

        <div class="grid-container">
            <!-- Current Plan -->
            <div class="card">
                <h2>Current Plan</h2>
                <p><strong><?php echo htmlspecialchars($planName); ?></strong></p>
                <p>Monthly Rental: $<?php echo htmlspecialchars($monthlyCost); ?></p>
                <p>Renewal Date: <?php echo htmlspecialchars($renewalDate); ?></p>
            </div>

            <!-- Usage Overview -->
            <div class="card">
                <h2>Usage Overview</h2>
                <p>Minutes: <?php echo htmlspecialchars($usedMinutes); ?>/200</p>
                <p>SMS: <?php echo htmlspecialchars($usedSMS); ?>/100</p>
                <p>Data: <?php echo htmlspecialchars($usedData); ?>GB/5GB</p>
            </div>

            <!-- Billing -->
            <div class="card">
                <h2>Billing</h2>
                <p>Outstanding Balance: $<?php echo htmlspecialchars($outstandingBalance); ?></p>
                <p>Due Date: <?php echo htmlspecialchars($dueDate); ?></p>
                <a href="#" class="button">Pay Now</a>
            </div>

            <!-- Notifications -->
            <div class="card">
                <h2>Notifications</h2>
                <?php if (!empty($notifications)): ?>
                    <ul>
                        <?php foreach ($notifications as $notification): ?>
                            <li><?php echo htmlspecialchars($notification); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No new notifications.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>




</body>
</html>
<?php

?>