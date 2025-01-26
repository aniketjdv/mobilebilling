<?php
// Include database configuration
include('db_config.php');

// Function to simulate usage
function simulateUsage($conn, $customerID, $usedMinutes, $usedSMS, $usedData) {
    // Fetch the current usage data for the customer
    $sql_fetch = "SELECT UsedMinutes, UsedSMS, UsedData FROM Cust_Usage WHERE CustomerID = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    if (!$stmt_fetch) {
        die("Error in fetch prepare statement: " . $conn->error);
    }
    $stmt_fetch->bind_param('i', $customerID);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    $currentUsage = $result->fetch_assoc();

    // Validate if customer data exists
    if (!$currentUsage) {
        die("Customer usage data not found.");
    }

    $currentMinutes = $currentUsage['UsedMinutes'];
    $currentSMS = $currentUsage['UsedSMS'];
    $currentData = $currentUsage['UsedData'];

    // Deduct the usage values
    $newMinutes = max(0, $currentMinutes - $usedMinutes);
    $newSMS = max(0, $currentSMS - $usedSMS);
    $newData = max(0, $currentData - $usedData);

    // Update the database with new values
    $sql_update = "UPDATE Cust_Usage SET UsedMinutes = ?, UsedSMS = ?, UsedData = ? WHERE CustomerID = ?";
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        die("Error in update prepare statement: " . $conn->error);
    }
    $stmt_update->bind_param('iiii', $newMinutes, $newSMS, $newData, $customerID);
    if ($stmt_update->execute()) {
        echo "Usage simulated successfully. New values: 
              Minutes: $newMinutes, SMS: $newSMS, Data: $newData GB";
    } else {
        echo "Error updating usage data: " . $conn->error;
    }
}

// Example: Simulate usage for customer with ID = 1
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerID = $_POST['customerID']; // Get customer ID from form
    $usedMinutes = $_POST['usedMinutes']; // Get minutes to deduct
    $usedSMS = $_POST['usedSMS']; // Get SMS to deduct
    $usedData = $_POST['usedData']; // Get data to deduct

    simulateUsage($conn, $customerID, $usedMinutes, $usedSMS, $usedData);
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulate Usage</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <div class="simulate-usage-container">
        <h1>Simulate Usage</h1>
        <form id="simulateForm" action="simulate_usage.php" method="POST">
            <label for="customerID">Customer ID:</label>
            <input type="number" id="customerID" name="customerID" required>

            <label for="usedMinutes">Minutes to Use:</label>
            <input type="number" id="usedMinutes" name="usedMinutes" min="0" required>

            <label for="usedSMS">SMS to Use:</label>
            <input type="number" id="usedSMS" name="usedSMS" min="0" required>

            <label for="usedData">Data to Use (GB):</label>
            <input type="number" id="usedData" name="usedData" step="0.1" min="0" required>

            <button type="submit">Simulate Usage</button>
        </form>
    </div>
</body>
</html>
