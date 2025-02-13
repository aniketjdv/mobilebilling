<?php
include('db_config.php');

// Fetch all available plans
$sql_plans = "SELECT PlanID, PlanName, MonthlyCost, FreeMinutes, FreeSMS, FreeData, Description FROM Plans";
$result_plans = $conn->query($sql_plans);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedPlan = $_POST['plan'];
    $customerID = $_SESSION['user_id']; // Assuming the user is logged in and session contains user_id

    // Fetch all available plans
$sql_plans1 = "SELECT PlanName, MonthlyCost, FreeMinutes, FreeSMS, FreeData FROM Plans WHERE PlanId =?";

$stmt_plan=$conn->prepare($sql_plans1);
$stmt_plan->bind_param("i",$selectedPlan);
$stmt_plan->execute();
$result_plan=$stmt_plan->get_result();
$planinfo=$result_plan->fetch_assoc();
$min=$planinfo["FreeMinutes"];
$sms=$planinfo["FreeSMS"];
$data=$planinfo["FreeData"];
$cost=$planinfo['MonthlyCost'];
$payment_method="Debit Card";

// echo  $billID, $customerID, $cost, $payment_method, $min,$sms,$data;
    // Insert payment record
    $sql_payment = "INSERT INTO Plans_Payment (CustomerID, Cost,PaymentDate ,PaymentMethod) 
                    VALUES (?, ?, NOW(),?)";
    $stmt_payment = $conn->prepare($sql_payment);

    $stmt_payment->bind_param("iis", $customerID, $cost, $payment_method);
    

// Execute the statement
if ($stmt_payment->execute() === TRUE) {
    echo "Payment updated";
} else {
    // Provide detailed error message
    echo "Error: " . $stmt_payment->error;
}

    $stmt_payment->close();



    // Assign the selected plan to the customer
    $sql_assign_plan = "INSERT INTO CustomerPlans (CustomerID, PlanID, StartDate, EndDate) 
                        VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH))";
    $stmt_assign_plan = $conn->prepare($sql_assign_plan);
    $stmt_assign_plan->bind_param('ii', $customerID, $selectedPlan);

    if ($stmt_assign_plan->execute()) {
        
        echo "Plan successfully assigned!";
        $_SESSION['plan']=$plan['PlanName'];
        $_SESSION['plan_flag']=True;

        $message="Your payment of Rs ".$cost." for your New plan has been received.";
    $sql_notification="INSERT INTO `Notifications` ( `CustomerID`, `Message`, `SentDate`, `IsRead`) VALUES (?, ?, current_timestamp(), '0')";
   $stmt_notification=$conn->prepare($sql_notification);
   $stmt_notification->bind_param('is',$customerID,$message);
   $stmt_notification->execute();
    } else {
        echo "Error: " . $stmt_assign_plan->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select a Plan</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <h2>Select a Plan</h2>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th>Monthly Cost</th>
                    <th>Select</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($plan = $result_plans->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($plan['PlanName']); ?></td>
                        <td><span class="currency">&#x20B9</span><?php echo htmlspecialchars($plan['MonthlyCost']); ?></td>
                        <td>
                            <input type="radio" name="plan" value="<?php echo htmlspecialchars($plan['PlanID']); ?>" required>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <button type="submit" id="buy-plan-btn" onclick="showmessage()">Buy Plan</button>
    </form>


    <div class="yourplan">
       <?php echo $_SESSION['plan'];
       ?>
    </div>
    <script>
        function showmessage(){
            alert("Transaction Succesful")
            location.reload();
        }
    </script>
</body>
</html>