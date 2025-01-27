<?php
// Include the database configuration file
include('db_config.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['fullname'];
    $email = $_POST['email'];
    $phno=$_POST['phno'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = $_POST['role']; // Get the role from the form (admin or customer)

    // Validate form fields
    if ($password !== $confirmPassword) {
        echo "Passwords do not match.";
    } else {
        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert user data into the database
        $sql = "INSERT INTO Customers (FullName, Email,PhoneNumber, PasswordHash, Role) VALUES (?, ?,?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssiss', $fullName, $email,$phno, $passwordHash, $role);

        if ($stmt->execute()) {
            echo "Signup successful! <a href='login.php'>Login here</a>";
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
    <h2>Signup</h2>
    <form method="POST">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="phno">Phone No:</label>
        <input type="phno" id="phno" name="phno" required><br><br>


        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>



        <label for="role">Role:</label>
        <select id="role" name="role">
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <button type="submit">Signup</button>
    </form>
</body>
</html>
