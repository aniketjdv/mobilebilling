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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Plans</title>
    <link rel="stylesheet" href="static/css/style.css"> <!-- Link to your CSS -->
</head>
<body>
<?php
    include("header.html");
    ?>
    <div class="plan_container">
    <h1>Available Plans</h1>
    <table border="0">
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
                    <td><?php echo htmlspecialchars($plan['FreeMinutes']); ?> mins</td>
                    <td><?php echo htmlspecialchars($plan['FreeSMS']); ?> SMS</td>
                    <td><?php echo htmlspecialchars($plan['FreeData']); ?> GB</td>
                    <td><?php echo htmlspecialchars($plan['Description']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    </div>
</body>
</html>