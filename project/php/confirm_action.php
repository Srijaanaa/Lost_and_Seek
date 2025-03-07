<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

function alertAndRedirect($message, $redirectUrl) {
    echo "<script>alert('$message'); window.location.href = '$redirectUrl';</script>";
    exit();
}

function executeQuery($conn, $sql, $params, $successMessage, $errorMessage, $redirectUrl = 'admin.php') {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        alertAndRedirect($errorMessage, $redirectUrl);
    }
    $stmt->bind_param(...$params);
    if ($stmt->execute()) {
        alertAndRedirect($successMessage, $redirectUrl);
    } else {
        alertAndRedirect($errorMessage, $redirectUrl);
    }
}

function handleItemAction($conn, $item_id, $action) {
    switch ($action) {
        case 'approve':
            $sql = "UPDATE items SET status = 'found', item_type = 'Found' WHERE id = ?";
            executeQuery($conn, $sql, ["i", $item_id], 'Item confirmed as found.', 'Error confirming item.');
            break;
        case 'reject':
            $sql = "UPDATE items SET status = 'rejected' WHERE id = ?";
            executeQuery($conn, $sql, ["i", $item_id], 'Item rejected.', 'Error rejecting item.');
            break;
        case 'delete':
            $sql = "UPDATE items SET visibility = 'hidden' WHERE id = ?";
            executeQuery($conn, $sql, ["i", $item_id], 'Item removed from dashboard (soft delete).', 'Error removing item.');
            break;
        case 'restore':
            $sql = "UPDATE items SET visibility = 'visible' WHERE id = ?";
            executeQuery($conn, $sql, ["i", $item_id], 'Item restored to dashboard.', 'Error restoring item.');
            break;
        default:
            alertAndRedirect('Invalid action for item.', 'admin.php');
    }
}

function handleMatchedItemAction($conn, $matched_item_id, $action) {
    // Check if the match exists first
    $sqlCheckMatch = "SELECT * FROM matched_items WHERE id = ? LIMIT 1";
    $stmtCheckMatch = $conn->prepare($sqlCheckMatch);
    $stmtCheckMatch->bind_param("i", $matched_item_id);
    $stmtCheckMatch->execute();
    $resultCheckMatch = $stmtCheckMatch->get_result();

    if ($resultCheckMatch->num_rows == 0) {
        alertAndRedirect('Match not found or already processed.', 'admin.php');
    }

    switch ($action) {
        case 'approve':
            $sqlApproveMatch = "UPDATE matched_items SET match_status = 'approved' WHERE id = ?";
            executeQuery($conn, $sqlApproveMatch, ["i", $matched_item_id], 'Match approved successfully.', 'Error approving match.');
            break;
        case 'reject':
            $sqlRejectMatch = "UPDATE matched_items SET match_status = 'rejected' WHERE id = ?";
            executeQuery($conn, $sqlRejectMatch, ["i", $matched_item_id], 'Match rejected successfully.', 'Error rejecting match.');
            break;
        default:
            alertAndRedirect('Invalid action for matched item.', 'admin.php');
    }
}

function handleReportAction($conn, $reportId, $action) {
    switch ($action) {
        case 'approve':
            $sqlUpdate = "UPDATE reports SET status = 'resolved' WHERE id = ?";
            executeQuery($conn, $sqlUpdate, ["i", $reportId], 'Report approved.', 'Error approving report.');

            $sqlItem = "UPDATE items SET status = 'found' WHERE id = (SELECT item_id FROM reports WHERE id = ?)";
            executeQuery($conn, $sqlItem, ["i", $reportId], 'Item status updated to found.', 'Error updating item status.');
            break;
        case 'reject':
            $sqlUpdate = "UPDATE reports SET status = 'rejected' WHERE id = ?";
            executeQuery($conn, $sqlUpdate, ["i", $reportId], 'Report rejected.', 'Error rejecting report.');
            break;
        default:
            alertAndRedirect('Invalid action for report.', 'admin.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Item-related actions
    if (isset($_POST['item_id']) && isset($_POST['action'])) {
        $item_id = (int)$_POST['item_id'];
        $action = $_POST['action'];
        handleItemAction($conn, $item_id, $action);
    }

    // Matched Item actions
    elseif (isset($_POST['matched_item_id']) && isset($_POST['action'])) {
        $matched_item_id = (int)$_POST['matched_item_id'];
        $action = $_POST['action'];
        handleMatchedItemAction($conn, $matched_item_id, $action);
    }

    // Report actions
    elseif (isset($_POST['report_id']) && isset($_POST['action'])) {
        $reportId = (int)$_POST['report_id'];
        $action = $_POST['action'];
        handleReportAction($conn, $reportId, $action);
    }
}

$conn->close();
?>
