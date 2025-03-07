<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['user_id'], $_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['role'] === 'admin' ? 'admin' : 'user';

    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_role, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('User role updated successfully.'); window.location.href = 'admin.php';</script>";
    } else {
        echo "<script>alert('Error updating user role.'); window.location.href = 'admin.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: admin.php");
    exit();
}
?>
