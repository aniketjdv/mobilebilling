<?php
// Include the database configuration file
include('db_config.php');
$signup_flag=False;
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['fullname'];
    $email = $_POST['email'];
    $phno=$_POST['phno'];
    $address=$_POST['addr'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role ='customer'; 
    
    // Validate form fields
    if ($password !== $confirmPassword) {
        echo "Passwords do not match.";
    } else {
        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert user data into the database
        $sql = "INSERT INTO Customers (FullName, Email,PhoneNumber,Address, PasswordHash, Role) VALUES (?, ?,?, ?, ?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssisss', $fullName, $email,$phno, $address,$passwordHash, $role);

        if ($stmt->execute()) {
            $signup_flag=True;
          
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="static/css/form.css">
</head>
<body>
<div class="form_container">
    <h2>Signup</h2>
    <form method="POST">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="phno">Phone No:</label>
        <input type="text" id="phno" name="phno" required><br><br>

        <label for="addr">Address</label>
        <textarea id="addr" name="addr" row="4" column="50" required></textarea><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>



        <!-- <label for="role">Role:</label>
        <select id="role" name="role" default="customer" disabled>
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
        </select><br><br> -->

        <button type="submit">Signup</button>
    </form>
    <?if($signup_flag==True){
     echo "<h3 style='color:Green'>Signup successful! </h3><br>
    
    <button type='submit' onclick='redirectpage()'>Login</button>";
    }
    $signup_flag=false;
    ?>
   
</div>

<script>
    function redirectpage(){
        window.location.href="login.php";
    }
</script>
</body>

</html>
