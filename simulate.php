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
$sql_usage = "
    SELECT 
        p.PlanName, 
        p.FreeMinutes, 
        p.FreeSMS, 
        p.FreeData, 
        cp.UsedMinutes, 
        cp.UsedSMS, 
        cp.UsedData
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
    die("No plan assigned to the customer.");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usedMinutes = isset($_POST['used_minutes']) ? (int)$_POST['used_minutes'] : 0;
    $usedSMS = isset($_POST['used_sms']) ? (int)$_POST['used_sms'] : 0;
    $usedData = isset($_POST['used_data']) ? (float)$_POST['used_data'] : 0;

    $customerPlanID =23; // Replace with a valid CustomerPlanID dynamically fetched

    $sql_update = "
        UPDATE `Cust_Usage`
        SET 
            UsedMinutes = LEAST(UsedMinutes + ?, ?),
            UsedSMS = LEAST(UsedSMS + ?, ?),
            UsedData = LEAST(UsedData + ?, ?)
        WHERE CustomerPlanID = ?
    ";

    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt_update->bind_param(
        'iiiiiii',
        $usedMinutes, $usage['FreeMinutes'],
        $usedSMS, $usage['FreeSMS'],
        $usedData, $usage['FreeData'],
        $customerPlanID
    );

    if ($stmt_update->execute()) {
        echo "Usage updated successfully!";
    } else {
        echo "Error updating usage: " . $stmt_update->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulate Usage</title>
</head>
<body>
    <h2>Plan Details</h2>
    <table>
        <tr>
            <th>Plan Name</th>
            <td><?php echo htmlspecialchars($usage['PlanName']); ?></td>
        </tr>
        <tr>
            <th>Free Minutes</th>
            <td><?php echo htmlspecialchars($usage['FreeMinutes'] - $usage['UsedMinutes']) . " mins"; ?></td>
        </tr>
        <tr>
            <th>Free SMS</th>
            <td><?php echo htmlspecialchars($usage['FreeSMS'] - $usage['UsedSMS']) . " SMS"; ?></td>
        </tr>
        <tr>
            <th>Free Data</th>
            <td><?php echo htmlspecialchars($usage['FreeData'] - $usage['UsedData']) . " GB"; ?></td>
        </tr>
    </table>

    <h2>Simulate Usage</h2>
    <form method="POST">
        <label for="used_minutes">Minutes Used:</label>
        <input type="number" id="used_minutes" name="used_minutes" min="0"><br><br>

        <label for="used_sms">SMS Used:</label>
        <input type="number" id="used_sms" name="used_sms" min="0"><br><br>

        <label for="used_data">Data Used (GB):</label>
        <input type="number" step="0.01" id="used_data" name="used_data" min="0"><br><br>

        <button type="submit">Simulate Usage</button>
    </form>
</body>
</html>
