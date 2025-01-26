<?php
session_start();
include('db_config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$customerID = $_SESSION['user_id'];

// Function to simulate usage
function simulateUsage($conn, $customerID) {
    $callMinutes = rand(50, 500); // Random call minutes
    $smsCount = rand(10, 100);    // Random SMS count
    $dataUsed = rand(1, 10);      // Random data usage in GB
    $date = date('Y-m-d');

    // Insert into the Cust_Usage table
    $sql = "INSERT INTO Cust_Usage (CustomerID, UsedMinutes, UsedSMS, UsedData, Date) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiids', $customerID, $callMinutes, $smsCount, $dataUsed, $date);
    
    if ($stmt->execute()) {
        return [
            'callMinutes' => $callMinutes,
            'smsCount' => $smsCount,
            'dataUsed' => $dataUsed
        ];
    } else {
        return false;
    }
}

// Simulate usage
$usage = simulateUsage($conn, $customerID);

$conn->close();
?>
<?php
include('db_config.php');


// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$customerID = $_SESSION['user_id'];

// Fetch the latest usage data
$sql = "SELECT UsedMinutes, UsedSMS, UsedData, Date 
        FROM Cust_Usage 
        WHERE CustomerID = ? 
        ORDER BY Date DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $customerID);
$stmt->execute();
$result = $stmt->get_result();
$usage = $result->fetch_assoc();
$conn->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulate Usage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .usage-details {
            margin-top: 20px;
        }
        .usage-details div {
            margin: 10px 0;
        }
        .simulate-btn {
            display: block;
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .simulate-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<?php
    include("header.html");
    ?>
    <div class="container">
        <h1>Simulate Usage</h1>

        <?php if ($usage): ?>
        <div class="usage-details">
            <h3>Latest Usage</h3>
            <div><strong>Call Minutes:</strong> <?php echo htmlspecialchars($usage['UsedMinutes']); ?></div>
            <div><strong>SMS:</strong> <?php echo htmlspecialchars($usage['UsedSMS']); ?></div>
            <div><strong>Data Used:</strong> <?php echo htmlspecialchars($usage['UsedData']); ?> GB</div>
            <div><strong>Date:</strong> <?php echo htmlspecialchars($usage['Date']); ?></div>
        </div>
        <?php else: ?>
        <p>No usage data found.</p>
        <?php endif; ?>

        <a href="simulate_usage_action.php" class="simulate-btn">Simulate New Usage</a>
    </div>
</body>
</html>
