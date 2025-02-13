<?php
// Include the database configuration file
include('db_config.php');

session_start();
$customerID=$_SESSION['user_id'];
// Fetch notifications from the Notification table
$sql = "SELECT NotificationID, message, isread FROM Notifications WHERE CustomerID=$customerID ORDER BY NotificationID DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="static/css/notifications.css">
</head>
<body>
    <?php include "header.html"; ?>

    <div class="container">
        <h2>Notifications</h2>
        <table class="notification-table">
            <thead>
                <tr>
                    
                    <th>Message</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="<?php echo $row['isread'] ? 'read' : 'unread'; ?>">
                            
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo $row['isread'] ? 'Read' : 'Unread'; ?></td>
                            <td>
                                <?php if ($row['isread'] == 0): ?>
                                    <a href="mark_read.php?NotificationID=<?php echo $row['NotificationID']; ?>" class="mark-read">Mark as Read</a>
                                <?php else: ?>
                                    <span class="read-text">✔ Read</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No notifications available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>

<?php $conn->close(); ?>
