<?php
include('db_config.php');

if (isset($_GET['NotificationID'])) {
    $notificationID = intval($_GET['NotificationID']);
    
    // Update isread status to 1 (mark as read)
    $sql = "UPDATE Notifications SET isread = 1 WHERE NotificationID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $notificationID);
    $stmt->execute();
    
    // Redirect back to notifications page
    header("Location: notification.php");
    exit;
}
?>
