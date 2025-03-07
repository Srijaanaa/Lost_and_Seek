<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['lost_item_id']) || !is_numeric($_POST['lost_item_id'])) {
    echo "<script>alert('Invalid item ID.'); window.location.href = 'dashboard.php';</script>";
    exit();
}

$lost_item_id = (int)$_POST['lost_item_id'];

$sqlAdmin = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
$resultAdmin = $conn->query($sqlAdmin);

if ($resultAdmin->num_rows > 0) {
    $rowAdmin = $resultAdmin->fetch_assoc();
    $adminId = $rowAdmin['id'];

    $message = "A lost item (ID: $lost_item_id) has been reported as found. Awaiting confirmation.";
    $sqlNotification = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
    $stmtNotif = $conn->prepare($sqlNotification);
    $stmtNotif->bind_param("is", $adminId, $message);

    if ($stmtNotif->execute()) {
        echo "<script>alert('Item reported as found. Awaiting admin confirmation.'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "<script>alert('Error creating notification.'); window.location.href = 'dashboard.php';</script>";
    }

    $stmtNotif->close();
} else {
    echo "<script>alert('No admin found. Cannot send notification.'); window.location.href = 'dashboard.php';</script>";
}

$conn->close();
?>
