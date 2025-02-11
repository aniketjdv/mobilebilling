<?php
// Include the database configuration file
include('db_config.php');

session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header('Location: login.php');
    exit;
}
$billingMonth = date('Y-m'); // Current month in YYYY-MM format
$dueDate=date('Y-m-d', strtotime('first day of next month'));

$status = "Pending"; // Default status
$insert_flag=False;
$insert_flag_error=False;
$flag_error=False;
$flag_sucsess=false;
$customerID = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $used_minutes=$_POST['minutes'];
    $used_sms=$_POST['sms'];
    $used_data=$_POST['data'];

    $sql="INSERT INTO `Cust_Usage` ( `CustomerID`, `UsedMinutes`, `UsedSMS`, `UsedData`, `UsageDate`, `CustomerPlanID`) VALUES ( $customerID,$used_minutes,$used_sms,$used_data,current_timestamp()	, '0')";
   
   
    if($conn->query($sql)){
        $flag_sucsess=True;
    }
    else{
        $flag_error=True;
    }

 // Check if the latest bill is fully paid (AmountDue = 0)
 $sql_check = "SELECT AmountDue FROM Billing WHERE CustomerID = ? ORDER BY BillID DESC LIMIT 1";
 $stmt_check = $conn->prepare($sql_check);
 $stmt_check->bind_param("i", $customerID);
 $stmt_check->execute();
 $result_check = $stmt_check->get_result();
 $latest_bill = $result_check->fetch_assoc();
 $stmt_check->close();

 if ($latest_bill && $latest_bill['AmountDue'] == 0) {
     // Reset usage data after payment completion
     $reset_sql = "DELETE FROM Cust_Usage WHERE CustomerID = ?";
     $stmt_reset = $conn->prepare($reset_sql);
     $stmt_reset->bind_param("i", $customerID);
     $stmt_reset->execute();
     $stmt_reset->close();
 }

    $sql_bill="
    SELECT 
        CustomerID, 
        SUM(UsedMinutes) AS TotalMinutes, 
        SUM(UsedSMS) AS TotalSMS, 
        SUM(UsedData) AS TotalData, 
        (SUM(UsedMinutes) * 1 + SUM(UsedSMS) * 0.5 + SUM(UsedData) * 10) AS TotalBill 
    FROM Cust_Usage
    WHERE CustomerID = ?
    GROUP BY CustomerID;
";
$stmt_bill = $conn->prepare($sql_bill);
$stmt_bill->bind_param("i", $customerID);
$stmt_bill->execute();
$result = $stmt_bill->get_result();
$bill = $result->fetch_assoc();


// If usage data is available, insert the bill into the database
if ($bill) {
    $totalBill = $bill['TotalBill'];

    // Insert data into Billing table
    $insertSQL = "INSERT INTO Billing (CustomerID, BillingMonth, AmountDue, DueDate, Status) VALUES (?, ?, ?, ?, ?)";

    $stmtInsert = $conn->prepare($insertSQL);
    $stmtInsert->bind_param("isiss", $customerID, $billingMonth, $totalBill, $dueDate, $status);
    //$stmtInsert->execute();
    if ($stmtInsert->execute()) {
       
        $insert_flag=True;
    } else {
       $insert_flag_error=True;
    }
    
    $stmtInsert->close();

}else {
    echo "<p>No usage records found to generate a bill.</p>";
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
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <? include("header.html")?>
    <form action="simulate2.php" method="POST">
    <label for="minutes">Minutes Used</label><input type="number" id="minute" name="minutes" require><br>
    <label for="sms">SMS Used</label><input type="number" id="sms" name="sms" require><br>
    <label for="data">Data Used</label><input type="number" id="data" name="data" require><br>
   
    <button>Submit</button>
    </form>
    <button onclick="genrate()">Genrate</button>
    <?if($flag_error==True){
    echo"<h4>Please fill all fiealds</h4>";
   }?>
    <?if($flag_sucsess==True){
    echo"<h4>Successfully Sumbited</h4>";
   }?>

   <?if ($insert_flag==True){
     echo "<p>Billing record added successfully!</p>";
   }
   if($insert_flag_error==True){
    echo "<p>Error inserting billing record: </p>";
   }
   
   ?>
    <script src="static/js/simulate.js"></script>
</body>
</html>
