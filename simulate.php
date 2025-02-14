<?php
// Include the database configuration file
include('db_config.php');

session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit;
}

$customerID = $_SESSION['user_id'];

// Fetch customer's plan and usage details
$sql_usage = "SELECT 
        p.PlanName, 
        p.FreeMinutes, 
        p.FreeSMS, 
        p.FreeData, 
        cp.UsedMinutes, 
        cp.UsedSMS, 
        cp.UsedData,
        cp.CustomerPlanID
    FROM CustomerPlans cp
    JOIN Plans p ON cp.PlanID = p.PlanID
    WHERE cp.CustomerID = ?
";
$stmt_usage = $conn->prepare($sql_usage);
$stmt_usage->bind_param('i', $customerID);
$stmt_usage->execute();
$result_usage = $stmt_usage->get_result();
$usage = $result_usage->fetch_assoc();

if (!$usage) {
    die("<h3 style='color:red;'>No active plan assigned to the customer.</h3>");
}

$customerPlanID = $usage['CustomerPlanID'];
$freeMinutes = $usage['FreeMinutes'];
$freeSMS = $usage['FreeSMS'];
$freeData = $usage['FreeData'];

// Check if all free usage is exhausted
$remainingMinutes = max(0, $freeMinutes - $usage['UsedMinutes']);
$remainingSMS = max(0, $freeSMS - $usage['UsedSMS']);
$remainingData = max(0, $freeData - $usage['UsedData']);

if ($remainingMinutes == 0 && $remainingSMS == 0 && $remainingData == 0) {
    // Delete the plan since the customer has exhausted all free resources
    $sql_delete = "DELETE FROM CustomerPlans WHERE CustomerPlanID = ?";
    $_SESSION['plan_flag']=False;
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $customerPlanID);
    $stmt_delete->execute();
    $stmt_delete->close();
    echo "<h3 style='color:red;'>Your plan has been fully used and deleted.</h3>";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usedMinutes = isset($_POST['used_minutes']) ? (int)$_POST['used_minutes'] : 0;
    $usedSMS = isset($_POST['used_sms']) ? (int)$_POST['used_sms'] : 0;
    $usedData = isset($_POST['used_data']) ? (float)$_POST['used_data'] : 0;

    // Ensure the update does not exceed free limits
    $updateSQL = "UPDATE CustomerPlans SET 
            UsedMinutes = LEAST(UsedMinutes + ?, ?),
            UsedSMS = LEAST(UsedSMS + ?, ?),
            UsedData = LEAST(UsedData + ?, ?)
        WHERE CustomerPlanID = ?
    ";

    $stmt_update = $conn->prepare($updateSQL);
    if (!$stmt_update) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt_update->bind_param(
        "iiiiidd",
        $usedMinutes, $freeMinutes,
        $usedSMS, $freeSMS,
        $usedData, $freeData,
        $customerPlanID
    );

    if ($stmt_update->execute()) {
        echo "<h3 style='color:green;'>Usage updated successfully!</h3>";
        // Redirect to usage.php after processing
header("Location: usage.php");
    } else {
        echo "<h3 style='color:red;'>Error updating usage: " . $stmt_update->error . "</h3>";
    }
}

$stmt_usage->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulate Usage</title>
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="stylesheet" href="static/css/simulate_style.css">
</head>
<body>
    <?php include "header.html"; ?>
    <div class="simulate_container">
    <h2>Plan Details</h2>
    <?php if ($remainingMinutes > 0 || $remainingSMS > 0 || $remainingData > 0): ?>
        <table>
            <tr>
                <th>Plan Name</th>
                <td><?php echo htmlspecialchars($usage['PlanName']); ?></td>
            </tr>
            <tr>
                <th>Remaining Minutes</th>
                <td><?php echo $remainingMinutes . " mins"; ?></td>
            </tr>
            <tr>
                <th>Remaining SMS</th>
                <td><?php echo $remainingSMS . " SMS"; ?></td>
            </tr>
            <tr>
                <th>Remaining Data</th>
                <td><?php echo $remainingData . " GB"; ?></td>
            </tr>
        </table>

        <h2>Simulate Usage</h2>
        <form method="POST">
            <label for="used_minutes">Minutes Used:</label>
            <input type="number" id="used_minutes" name="used_minutes" min="0" max="<?php echo $remainingMinutes; ?>"><br><br>

            <label for="used_sms">SMS Used:</label>
            <input type="number" id="used_sms" name="used_sms" min="0" max="<?php echo $remainingSMS; ?>"><br><br>

            <label for="used_data">Data Used (GB):</label>
            <input type="number"  id="used_data" name="used_data" min="0" max="<?php echo $remainingData; ?>"><br><br>

            <button type="submit">Simulate Usage</button>
        </form>
    <?php else: ?>
        <h3 style="color:red;">You have used all your resources. Your plan has been deleted.</h3>
    <?php endif; ?>
    </div>
</body>
</html>
