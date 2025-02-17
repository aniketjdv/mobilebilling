<?php
session_start();

include("db_config.php");


if(isset($_SESSION['user_id'])){
    $userID = $_SESSION['user_id'];
}
else{
    header("Location:admin_login.php");
}

    if(!($_SESSION['role']=='admin')) {
        $_SESSION['admin_state']=True;
       
        exit();
    }
    else{
        
    // Handle Deleting Users
    if (isset($_GET['delete_user'])) {
        $userID = $_GET['delete_user'];
        $conn->query("DELETE FROM Customers WHERE CustomerID = $userID");
        header("Location: admin.php");
    }
    
    // Handle Adding Plans
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_plan'])) {
        $name = $_POST['name'];
        $cost = $_POST['cost'];
        $minutes = $_POST['minutes'];
        $sms = $_POST['sms'];
        $data = $_POST['data'];
        $desc = $_POST['description'];
    
        $stmt = $conn->prepare("INSERT INTO Plans (PlanName, MonthlyCost, FreeMinutes, FreeSMS, FreeData, Description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiiis", $name, $cost, $minutes, $sms, $data, $desc);
        $stmt->execute();
        header("Location: admin.php");
    }
    
    // Handle Deleting Plans
    if (isset($_GET['delete_plan'])) {
        $planID = $_GET['delete_plan'];
        $conn->query("DELETE FROM Plans WHERE PlanID = $planID");
        header("Location: admin.php");
    }
    
    // Handle Resolving Support Messages
    if (isset($_GET['resolve_message'])) {
        $msgID = $_GET['resolve_message'];
        $conn->query("UPDATE SupportMessages SET Status='Resolved' WHERE MessageID = $msgID");
        header("Location: admin.php");
    }
    
  $sql=  "SELECT sm.*, u.Fullname 
    FROM SupportMessages sm
    JOIN Customers u ON sm.CustomerID = u.CustomerID
    ORDER BY sm.Timestamp DESC";
    // Fetch Data
    $users = $conn->query("SELECT * FROM Customers");
    $plans = $conn->query("SELECT * FROM Plans");
   //$messages = $conn->query("SELECT * FROM SupportMessages");
     $messages=$conn->query($sql);
   // $user_name=$conn->query("Select Fullname FROM Customer where CustomerId = ?");

}


    ?>
    
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="admin.css">
        <link rel="stylesheet" href="staitc/css/chat.css">
        <script>
            function confirmDelete(type, id) {
                return confirm(`Are you sure you want to delete this ${type}?`);
            }
        </script>
        <link rel="stylesheet" href="static/css/admin_style.css">
    </head>
    <body>
        <h1>Admin Panel</h1>
        <nav>
            <ul>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    
        <!-- Manage Users Section -->
        <h2>Manage Users</h2>
        <table>
            <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
            <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $user['FullName'] ?></td>
                <td><?= $user['Email'] ?></td>
                <td>
                    <a href="?delete_user=<?= $user['CustomerID'] ?>" onclick="return confirmDelete('user', <?= $user['CustomerID'] ?>);">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    
        <!-- Manage Plans Section -->
        <h2>Manage Plans</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Plan Name" required>
            <input type="number" name="cost" placeholder="Monthly Cost" required>
            <input type="number" name="minutes" placeholder="Free Minutes">
            <input type="number" name="sms" placeholder="Free SMS">
            <input type="text" name="data" placeholder="Free Data (GB)">
            <textarea name="description" placeholder="Plan Description"></textarea>
            <button type="submit" name="add_plan">Add Plan</button>
        </form>
    
        <table>
            <tr><th>Name</th><th>Cost</th><th>Actions</th></tr>
            <?php while ($plan = $plans->fetch_assoc()): ?>
            <tr>
                <td><?= $plan['PlanName'] ?></td>
                <td>₹<?= $plan['MonthlyCost'] ?></td>
                <td>
                    <a href="?delete_plan=<?= $plan['PlanID'] ?>" onclick="return confirmDelete('plan', <?= $plan['PlanID'] ?>);">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    
        <!-- Customer Support Messages Section -->
        <h2>Customer Support</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Message</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($msg = $messages->fetch_assoc()): ?>
         <tr>
        <td><?= htmlspecialchars($msg['Fullname']) ?></td> 
        <td><?= htmlspecialchars($msg['Message']) ?></td>
        <td><?= htmlspecialchars($msg['Status']) ?></td>
        <td>
            <a href="?resolve_message=<?= $msg['MessageID'] ?>">Mark Resolved</a>
        </td>
        </tr>
        <?php endwhile; ?>
        </table>

       
  
    </body>
    </html>
    