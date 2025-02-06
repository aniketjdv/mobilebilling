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

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $used_minutes=$_POST['minutes'];
    $used_sms=$_POST['sms'];
    $used_data=$_POST['data'];

    $sql="INSERT INTO `Cust_Usage` ( `CustomerID`, `UsedMinutes`, `UsedSMS`, `UsedData`, `UsageDate`, `CustomerPlanID`) VALUES ( $customerID,$used_minutes,$used_sms,$used_data,current_timestamp()	, '0')";
    
    if($conn->query($sql)){
        echo "done";
    }
    else{
        echo "faild";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulate</title>
</head>
<body>
    <form action="simulate2.php" method="POST">
    <label for="minutes">Minutes Used</label><input type="text" name="minutes"><br>
    <label for="sms">SMS Used</label><input type="text" name="sms"><br>
    <label for="data">Data Used</label><input type="text" name="data"><br>
    <input type="button" value="Genrate">
    <button>Submit</button>
    </form>
</body>
</html>
