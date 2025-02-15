
<?php
// Include the database configuration file
include('db_config.php');

session_start();

if(!isset($_SESSION["user_id"]))
{
    header('Location: login.php');
}

$_SESSION['plan_flag']=False;
// Set the customer ID (for demonstration, this is static; in production, use session data or authentication)
$customerID = $_SESSION['user_id'];

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
    SELECT p.PlanName, p.MonthlyCost, p.FreeMinutes, p.FreeSMS, p.FreeData,cp.StartDate, cp.EndDate
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
$freeMinutes=$plan['FreeMinutes'] ?? 0;
$freeSMS=$plan['FreeSMS'] ?? 0;
$freeData=$plan['FreeData'] ?? 0;
if($plan['PlanName']!=NULL){
    $_SESSION['plan']=$plan['PlanName'];
    $_SESSION['plan_flag']=True;
}else{
    $_SESSION['plan']=NULL;
}
// Fetch usage data
$sql_usage = "SELECT SUM(UsedMinutes) AS TotalMinutes, 
        SUM(UsedSMS) AS TotalSMS, 
        SUM(UsedData) AS TotalData
          FROM Cust_Usage WHERE CustomerID = ? ORDER BY UsageID DESC";
$stmt_usage = $conn->prepare($sql_usage);
$stmt_usage->bind_param('i', $customerID);
$stmt_usage->execute();
$result_usage = $stmt_usage->get_result();
$usage = $result_usage->fetch_assoc();
$usedMinutes = $usage['TotalMinutes'] ?? 0;
$usedSMS = $usage['TotalSMS'] ?? 0;
$usedData = $usage['TotalData'] ?? 0;

//fetch plan usage
$plan_sql="SELECT UsedMinutes,UsedSMS,UsedData FROM CustomerPlans WHERE CustomerID=$customerID";
$stmt_plan=$conn->prepare($plan_sql);
$stmt_plan->execute();
$result_plan=$stmt_plan->get_result();
$plan_usage=$result_plan->fetch_assoc();
$plan_usedMinutes = $plan_usage['UsedMinutes'] ?? 0;
$plan_usedSMS = $plan_usage['UsedSMS'] ?? 0;
$plan_usedData = $plan_usage['UsedData'] ?? 0;

// Fetch billing details
$sql_billing = "SELECT AmountDue, DueDate FROM Billing WHERE CustomerID = ? AND Status = 'Pending' 
                ORDER BY BillID DESC 
                LIMIT 1";
$stmt_billing = $conn->prepare($sql_billing);
$stmt_billing->bind_param("i", $customerID);
$stmt_billing->execute();
$result_billing = $stmt_billing->get_result();
if ($result_billing->num_rows > 0) {
    $billing = $result_billing->fetch_assoc();
    $amountDue = $billing['AmountDue'];
    $dueDate = $billing['DueDate'];

    $outstandingBalance = $billing['AmountDue'];

} else {
    $outstandingBalance =  0;
    $dueDate =  'N/A';
}

// Fetch notifications
$sql_notifications = "SELECT Message FROM Notifications WHERE CustomerID = ? AND IsRead = FALSE ORDER BY NotificationID DESC";
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
    <link rel="stylesheet" href="static/css/index.css">
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
                <p>Monthly Rental: <span class="currency">&#x20B9</span><?php echo htmlspecialchars($monthlyCost); ?></p>
                <p>Renewal Date: <?php echo htmlspecialchars($renewalDate); ?></p>
            </div>

            <!-- Usage Overview -->
           <?php
           if($_SESSION['plan_flag']==True){
           echo" <div class='card'>
                <h2>Usage Overview</h2>
                <p>Minutes:$plan_usedMinutes / $freeMinutes</p>
                <p>SMS:$plan_usedSMS / $freeSMS</p>
                <p>Data:$plan_usedData / $freeData</p>
            </div>";
           }
           else{
           echo" <div class='card'>
                <h2>Usage Overview</h2>
                <p>Minutes: $usedMinutes</p>
                <p>SMS: $usedSMS</p>
                <p>Data: $usedData GB</p>
            </div>";
           }
            ?>
            <!-- Billing -->
            <div class="card">
                <h2>Billing</h2>
                <p>Outstanding Balance: <span class="currency">&#x20B9</span><?php echo htmlspecialchars($outstandingBalance); ?></p>
                <p>Due Date: <?php echo htmlspecialchars($dueDate); ?></p>
                <a href="payment.php" class="button">Pay Now</a>
            </div>

            <!-- Notifications -->
            <div class="card">
                <h2>Notifications</h2>
                <?php if (!empty($notifications)): ?>
                    <ol>
                        <?php foreach ($notifications as $notification): ?>
                            <li><?php echo htmlspecialchars($notification); ?></li>
                        <?php endforeach; ?>
                    </ol>
                <?php else: ?>
                    <p>No new notifications.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
