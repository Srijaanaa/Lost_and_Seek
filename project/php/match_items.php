<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['found_item_id'], $_POST['lost_item_id']) || !is_numeric($_POST['found_item_id']) || !is_numeric($_POST['lost_item_id'])) {
    echo "<script>alert('Invalid item IDs.'); window.location.href = 'admin.php';</script>";
    exit();
}

$found_item_id = (int)$_POST['found_item_id'];
$lost_item_id = (int)$_POST['lost_item_id'];

$sql = "INSERT INTO matched_items (lost_item_id, found_item_id, admin_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$admin_id = $_SESSION['user_id']; 
$stmt->bind_param("iii", $lost_item_id, $found_item_id, $admin_id);

if ($stmt->execute()) {
    $updateFoundSql = "UPDATE items SET status = 'matched' WHERE id = ?";
    $updateStmt = $conn->prepare($updateFoundSql);
    $updateStmt->bind_param("i", $found_item_id);
    $updateStmt->execute();
    
    echo "<script>alert('Found and Lost items matched successfully!'); window.location.href = 'admin.php';</script>";
} else {
    echo "<script>alert('Error matching items.'); window.location.href = 'admin.php';</script>";
}

$stmt->close();
$conn->close();
?>
