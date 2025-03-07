<?php
session_start();
include 'connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid report ID.'); window.location.href = 'myreports.php';</script>";
    exit();
}

$report_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');

try {
    if (!$is_admin) {
        $sqlCheck = "SELECT id FROM items WHERE id = ? AND user_id = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("ii", $report_id, $user_id);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows === 0) {
            $redirect_url = 'myreports.php';
            echo "<script>alert('Report not found or you are not authorized to remove it from the dashboard.'); window.location.href = 'myreports.php';</script>";
            exit();
        }
    }

    if ($is_admin) {
        $sql = "UPDATE items SET visibility = 'hidden' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $report_id);
    } else {
        $sql = "UPDATE items SET visibility = 'hidden' WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $report_id, $user_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Report deleted successfully.'); window.location.href = 'myreports.php';</script>";
    } else {
        throw new Exception("Error updating report visibility: " . $stmt->error);
    }
} catch (Exception $e) {
    echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href = 'myreports.php';</script>";
}

$stmt->close();
$conn->close();
?>
