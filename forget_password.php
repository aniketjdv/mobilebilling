<?php
include('db_config.php');
$succ_flag=False;
$err_flag=False;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists
    $sql = "SELECT CustomerID FROM Customers WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));  // Generate a secure token
        $expiry = date("Y-m-d H:i:s", strtotime("+12 hour")); // Token expires in 1 hour

        // Store token in database
        $updateSQL = "UPDATE Customers SET ResetToken = ?, TokenExpiry = ? WHERE Email = ?";
        $stmt = $conn->prepare($updateSQL);
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        // Demo: Show the reset link instead of emailing
        $succ_flag=True;
        

    } else {
        $err_flag=True;
       
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="static/css/auth.css">
</head>
<body>
<div class="form-container">
        <h2>Forgot Password</h2>
        <form method="POST">
            <label for="email">Enter your email:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Get Reset Link</button>
        </form>
        <?
        if($succ_flag){
        echo "<h3>Password Reset Link:</h3>";
        echo "<a href='reset_password.php?token=$token'>Click here to reset password</a>";
        }
        if($err_flag){
            echo "<h3 style='color:red;'>No account found with this email.</h3>";
        }
    
        ?>
        </div>
</body>
</html>
