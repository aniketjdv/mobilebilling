<?php
include('db_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT CustomerID, PasswordHash, Role FROM Customers WHERE Email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Error: " . $conn->error); // Debugging statement
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['PasswordHash'])) {
            session_start();
            $_SESSION['user_id'] = $user['CustomerID'];
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $user['Role'];

            if ($_SESSION['role'] === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
               header('Location: index.php');
               
            }
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No account found with this email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="static/css/form.css">
</head>
<body>
    <h2>Login</h2>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
