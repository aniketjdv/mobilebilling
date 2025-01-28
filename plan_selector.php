<?php
include('db_config.php');

// Fetch all available plans
$sql_plans = "SELECT PlanID, PlanName, MonthlyCost, FreeMinutes, FreeSMS, FreeData, Description FROM Plans";
$result_plans = $conn->query($sql_plans);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedPlan = $_POST['plan'];
    $customerID = $_SESSION['user_id']; // Assuming the user is logged in and session contains user_id

    // Assign the selected plan to the customer
    $sql_assign_plan = "INSERT INTO CustomerPlans (CustomerID, PlanID, StartDate, EndDate) 
                        VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH))";
    $stmt_assign_plan = $conn->prepare($sql_assign_plan);
    $stmt_assign_plan->bind_param('ii', $customerID, $selectedPlan);

    if ($stmt_assign_plan->execute()) {
        
        echo "Plan successfully assigned!";
        $_SESSION['plan']=$plan['PlanName'];
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
        <button type="submit">Select Plan</button>
    </form>


    <div class="yourplan">
       <?php echo $_SESSION['plan'];
       ?>
    </div>
</body>
</html>