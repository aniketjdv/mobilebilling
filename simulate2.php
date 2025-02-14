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
    <style>
        /* Reset default margin and padding */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7fa;
    color: #333;
   
    line-height: 1.6;
}

h1, h2, h3, h4 {
    color: #2f3b52;
    font-weight: bold;
}

h2 {
    margin-bottom: 20px;
    font-size: 24px;
}

/* Form Styling */
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

form label {
    font-size: 16px;
    margin-bottom: 8px;
    display: block;
    color: #2f3b52;
}

form input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}




/* Submit button */
form button {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
    margin-top: 10px;
}

form button:hover {
    background-color: #45a049;
}

/* Success/Error Messages */
h4 {
    color: #d9534f;
    font-size: 18px;
    margin-top: 10px;
    text-align: center;
}

p {
    color: #5bc0de;
    text-align: center;
    font-size: 16px;
}

button {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    display: block;
    margin-top: 20px;
    margin-left: auto;
    margin-right: auto;
}

button:hover {
    background-color: #0056b3;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    body {
        padding: 15px;
    }

    form {
        padding: 15px;
    }

    form label {
        font-size: 14px;
    }

    form input, form button {
        font-size: 14px;
        padding: 10px;
    }
}

@media screen and (max-width: 480px) {
    form {
        padding: 10px;
    }

    form label {
        font-size: 12px;
    }

    form input, form button {
        font-size: 12px;
        padding: 8px;
    }

    button {
        font-size: 14px;
        padding: 8px 16px;
    }
}



    </style>
</head>
<body>
    <? include("header.html")?>
    <form action="simulate2.php" method="POST">
    <label for="minutes">Minutes Used</label><input type="number" id="minute" name="minutes" min="0" require><br>
    <label for="sms">SMS Used</label><input type="number" id="sms" name="sms" min="0" require><br>
    <label for="data">Data Used</label><input type="number" id="data" name="data" min="0" require><br>
   
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
