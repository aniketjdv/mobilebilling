<?php
// Include the database configuration file
include('db_config.php');


// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit;
}

// Get the logged-in customer's ID
$customerID = $_SESSION['user_id'];

// Check if the form is submitted to remove the plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_plan'])) {
    // Remove the plan from the database
    $sql_remove_plan = "DELETE FROM CustomerPlans WHERE CustomerID = ?";
    $stmt_remove = $conn->prepare($sql_remove_plan);
    $stmt_remove->bind_param('i', $customerID);

    if ($stmt_remove->execute()) {
        echo "<p>Plan removed successfully.</p>";
        $plan = null; // Reset the plan to null after removal
    } else {
        echo "<p>Error removing plan: " . $conn->error . "</p>";
    }
}

// Fetch customer's plan details
$sql_plan = "
    SELECT 
        p.PlanID,
        p.PlanName,
        p.MonthlyCost,
        p.FreeMinutes,
        p.FreeSMS,
        p.FreeData,
        p.Description,
        cp.StartDate,
        cp.EndDate
    FROM CustomerPlans cp
    JOIN Plans p ON cp.PlanID = p.PlanID
    WHERE cp.CustomerID = ?
";
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

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Plan Information</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <h2>Your Plan Information</h2>
    <?php if ($plan): ?>
        <table>
            <tr>
                <th>Plan Name</th>
                <td><?php echo htmlspecialchars($plan['PlanName']); ?></td>
            </tr>
            <tr>
                <th>Monthly Cost</th>
                <td><span class="currency">&#x20B9</span><?php echo htmlspecialchars($plan['MonthlyCost']); ?></td>
            </tr>
            <tr>
                <th>Free Minutes</th>
                <td>
                    <?php echo $plan['FreeMinutes'] ? htmlspecialchars($plan['FreeMinutes']) . ' mins' : '∞'; ?>
                </td>
            </tr>
            <tr>
                <th>Free SMS</th>
                <td>
                    <?php echo $plan['FreeSMS'] ? htmlspecialchars($plan['FreeSMS']) . ' SMS' : '∞'; ?>
                </td>
            </tr>
            <tr>
                <th>Free Data</th>
                <td>
                    <?php echo $plan['FreeData'] ? htmlspecialchars($plan['FreeData']) . ' GB' : '∞'; ?>
                </td>
            </tr>
            <tr>
                <th>Description</th>
                <td><?php echo htmlspecialchars($plan['Description']); ?></td>
            </tr>
            <tr>
                <th>Plan Start Date</th>
                <td><?php echo htmlspecialchars($plan['StartDate']); ?></td>
            </tr>
            <tr>
                <th>Plan End Date</th>
                <td><?php echo htmlspecialchars($plan['EndDate']); ?></td>
            </tr>
        </table>
        <form method="POST">
    <button type="submit" name="remove_plan">Remove Plan</button>
</form>

    <?php else: ?>
        <p>You are not subscribed to any plan. Please select a plan.</p>
    <?php endif; ?>
</body>
</html>
