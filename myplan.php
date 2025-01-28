<?php
include('db_config.php');

session_start();

if(!isset($_SESSION["user_id"]))
{
    header('Location: login.php');
    exit;
}
else{
    $customerID = $_SESSION['user_id'];

    $sql_plan="SELECT * FROM Plans";
    $stmt_plan=$conn->prepare($sql_plan);
    $stmt_plan->execute();
    $result_plan=$stmt_plan->get_result();

    // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //     if (isset($_POST['plan'])){
    //     $selectedPlan = $_POST['plan'];}
    //    // $customerID = $_SESSION['user_id']; // Assuming the user is logged in and session contains user_id
    
    //     // Assign the selected plan to the customer
    //     $sql_assign_plan = "INSERT INTO CustomerPlans (CustomerID, PlanID, StartDate, EndDate) 
    //                         VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH))";
    //     $stmt_assign_plan = $conn->prepare($sql_assign_plan);
    //     $stmt_assign_plan->bind_param('ii', $customerID, $selectedPlan);
    
    //     if ($stmt_assign_plan->execute()) {
    //         echo "Plan successfully assigned!";
    //     } else {
    //         echo "Error: " . $stmt_assign_plan->error;
    //     }
    // }


}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Plans</title>
    <link rel="stylesheet" href="static/css/style.css"> <!-- Link to your CSS -->

    <style>
        table {
    width: 88%;
    border-collapse: collapse;
    margin: 20px;
    border-radius: 10px;
}

td {
    padding: 8px;
    text-align: left;
}
th {
    text-align: center;
    padding: 8px;
    background-color:rgb(247, 181, 1);
}
tr:nth-child(even) {
    background-color: #f2f2f2;
}
thead:first-child tr:first-child th:first-child {
    border-radius: 10px 0 0 0;

}

thead:first-child tr:last-child th:last-child {
    border-radius: 0 10px 0 0;

}
tbody tr:nth-child(even) td:last-child{
    background-color: #ffffff;
}
/* Add a wrapper for horizontal scrolling */
.table-responsive {
    overflow-x: auto; /* Allow horizontal scrolling on smaller screens */
    margin: 0 auto;
}

/* Adjust for smaller screens */
@media screen and (max-width: 768px) {
    table {
        font-size: 14px; /* Reduce font size */
    }

    td, th {
        padding: 6px; /* Reduce padding */
    }

    th {
        font-size: 16px; /* Larger font size for headers */
    }
}

@media screen and (max-width: 480px) {
    table {
        font-size: 12px;
    }

    td, th {
        padding: 4px;
    }
}
    </style>
</head>
<body>
<?php
    include("header.html");
    ?>
    <div class="plan_container">
    <h1>Available Plans</h1>
    <div class="table-responsive">
    <table border="0" class="plan_table">
        <thead>
            <tr>
                <th>Sr No.</th>
                <th>Plan Name</th>
                <th>Monthly Cost</th>
                <th>Free Minutes</th>
                <th>Free SMS</th>
                <th>Free Data (GB)</th>
                <th>Description</th>
                
            </tr>
        </thead>
        <tbody>
            <?php while ($plan = $result_plan->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($plan['PlanID']); ?></td>
                    <td><?php echo htmlspecialchars($plan['PlanName']); ?></td>
                    <td><span class="currency">&#x20B9</span><?php echo htmlspecialchars($plan['MonthlyCost']); ?></td>

                    <td><?php 
                        if($plan['FreeMinutes']==NULL){
                            echo '<span class="infinity-symbol">&infin;</span>';
                        }
                        else{
                        echo htmlspecialchars($plan['FreeMinutes']); 
                        }
                        ?> mins</td>
                    <td><?php 
                    if($plan['FreeSMS']==NULL){
                        echo '<span class="infinity-symbol">&infin;</span>';
                    }
                    else{
                    echo htmlspecialchars($plan['FreeSMS']); 
                    }
                    ?> SMS</td>

                    <td><?php 
                    if($plan['FreeData']==NULL){
                        echo '<span class="infinity-symbol">&infin;</span>';
                    }
                    else{
                    echo htmlspecialchars($plan['FreeData']); 
                    }
                    ?> GB</td>
                    
                    <td><?php echo htmlspecialchars($plan['Description']); ?></td>
                    
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
    
    <?php
    if(isset($_SESSION['plan'])){
        include('cust_plan.php');
    }
    else{
    include('plan_selector.php');
    }
    ?>
    


</body>
</html>