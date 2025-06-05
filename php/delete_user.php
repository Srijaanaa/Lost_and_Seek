<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    echo "<script>alert('Invalid User ID.'); window.location.href = 'admin.php';</script>";
    exit();
}

$user_id = (int)$_POST['user_id'];

if ($user_id == $_SESSION['user_id']) {
    echo "<script>alert('You cannot delete yourself.'); window.location.href = 'admin.php';</script>";
    exit();
}

$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo "<script>alert('User deleted successfully.'); window.location.href = 'admin.php';</script>";
} else {
    echo "<script>alert('Error deleting user.'); window.location.href = 'admin.php';</script>";
}

$stmt->close();
$conn->close();
?>
