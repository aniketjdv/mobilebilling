<?php
include('db_config.php');
$flag=False;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        die("<h3 style='color:red;'>Passwords do not match.</h3>");
    }

    // Check if token is valid
    $sql = "SELECT CustomerID FROM Customers WHERE ResetToken = ? AND TokenExpiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update password and clear reset token
        $updateSQL = "UPDATE Customers SET PasswordHash = ?, ResetToken = NULL, TokenExpiry = NULL WHERE CustomerID = ?";
        $stmt = $conn->prepare($updateSQL);
        $stmt->bind_param("si", $hashedPassword, $user['CustomerID']);
        $stmt->execute();

       
        $flag=True;
    } else {
        echo "<h3 style='color:red;'>Invalid or expired reset token.</h3>";
    }
} else {
    $token = $_GET['token'] ?? '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="static/css/auth.css">
</head>
<body>
<div class="form-container">
        <h2>Reset Password</h2>
        <form method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Reset Password</button>
        </form>
        <? if($flag){
         echo "<h3 style='color:green;'>Password reset successful!</h3>";
        echo "<button type='submit' onclick=go()>Go Back to Login </button>";
    }?>
    </div>
   
    <script>
        function go(){
            window.location.href="login.php";
        }
    </script>
</body>
</html>
