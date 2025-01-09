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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['item_id']) && isset($_POST['action'])) {
        $item_id = (int)$_POST['item_id'];  
        $action = $_POST['action'];

        switch ($action) {
            case 'approve':
                $sql = "UPDATE items SET status = 'found', item_type = 'Found' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    alertAndRedirect('Error preparing query.', 'admin.php');
                }
                $stmt->bind_param("i", $item_id);
                if ($stmt->execute()) {
                    alertAndRedirect('Item confirmed as found.', 'admin.php');
                } else {
                    alertAndRedirect('Error confirming item.', 'admin.php');
                }
                break;
            
            case 'reject':
                $sql = "UPDATE items SET status = 'rejected' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    alertAndRedirect('Error preparing query.', 'admin.php');
                }
                $stmt->bind_param("i", $item_id);
                if ($stmt->execute()) {
                    alertAndRedirect('Item rejected.', 'admin.php');
                } else {
                    alertAndRedirect('Error rejecting item.', 'admin.php');
                }
                break;

            case 'match':
                if (isset($_POST['lost_item_id'], $_POST['found_item_id'])) {
                    $lost_item_id = (int)$_POST['lost_item_id']; 
                    $found_item_id = (int)$_POST['found_item_id']; 

                    if ($lost_item_id && $found_item_id) {
                        $sqlLost = "SELECT id FROM items WHERE id = ? AND item_type = 'Lost' LIMIT 1";
                        $stmtLost = $conn->prepare($sqlLost);
                        $stmtLost->bind_param("i", $lost_item_id);
                        $stmtLost->execute();
                        $resultLost = $stmtLost->get_result();

                        $sqlFound = "SELECT id FROM items WHERE id = ? AND item_type = 'Found' LIMIT 1";
                        $stmtFound = $conn->prepare($sqlFound);
                        $stmtFound->bind_param("i", $found_item_id);
                        $stmtFound->execute();
                        $resultFound = $stmtFound->get_result();

                        if ($resultLost->num_rows == 0 || $resultFound->num_rows == 0) {
                            alertAndRedirect('One or both items do not exist or are not of the correct type.', 'admin.php');
                        }

                        $sqlMatchCheck = "SELECT id FROM matched_items WHERE lost_item_id = ? AND found_item_id = ? LIMIT 1";
                        $stmtMatchCheck = $conn->prepare($sqlMatchCheck);
                        $stmtMatchCheck->bind_param("ii", $lost_item_id, $found_item_id);
                        $stmtMatchCheck->execute();
                        $resultMatchCheck = $stmtMatchCheck->get_result();

                        if ($resultMatchCheck->num_rows > 0) {
                            alertAndRedirect('This pair has already been matched.', 'admin.php');
                        }

                        $sqlMatch = "INSERT INTO matched_items (lost_item_id, found_item_id, admin_id) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sqlMatch);
                        if ($stmt === false) {
                            alertAndRedirect('Error preparing query for matching items.', 'admin.php');
                        }
                        $admin_id = $_SESSION['user_id'];
                        $stmt->bind_param("iii", $lost_item_id, $found_item_id, $admin_id);

                        if ($stmt->execute()) {
                            alertAndRedirect('Items matched successfully!', 'admin.php');
                        } else {
                            alertAndRedirect('Error matching items. Please try again.', 'admin.php');
                        }
                    } else {
                        alertAndRedirect('Invalid item IDs.', 'admin.php');
                    }
                }
                break;
            
            case 'delete':
                $sql = "UPDATE items SET visibility = 'hidden' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    alertAndRedirect('Error preparing query.', 'admin.php');
                }
                $stmt->bind_param("i", $item_id);
                if ($stmt->execute()) {
                    alertAndRedirect('Item removed from dashboard (soft delete).', 'admin.php');
                } else {
                    alertAndRedirect('Error removing item.', 'admin.php');
                }
                break;

            case 'restore':
                $sql = "UPDATE items SET visibility = 'visible' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    alertAndRedirect('Error preparing query.', 'admin.php');
                }
                $stmt->bind_param("i", $item_id);
                if ($stmt->execute()) {
                    alertAndRedirect('Item restored to dashboard.', 'admin.php');
                } else {
                    alertAndRedirect('Error restoring item.', 'admin.php');
                }
                break;

            default:
                alertAndRedirect('Invalid action.', 'admin.php');
                break;
        }
    }

    elseif (isset($_POST['report_id']) && isset($_POST['action'])) {
        $reportId = (int)$_POST['report_id']; 
        $action = $_POST['action'];

        if ($action == 'approve') {
            $sqlUpdate = "UPDATE reports SET status = 'resolved' WHERE id = ?";
            $stmt = $conn->prepare($sqlUpdate);
            if ($stmt === false) {
                alertAndRedirect('Error preparing query for report approval.', 'admin.php');
            }
            $stmt->bind_param("i", $reportId);
            if (!$stmt->execute()) {
                alertAndRedirect('Error executing report approval.', 'admin.php');
            }

            $sqlItem = "UPDATE items SET status = 'found' WHERE id = (SELECT item_id FROM reports WHERE id = ?)";
            $stmtItem = $conn->prepare($sqlItem);
            if ($stmtItem === false) {
                alertAndRedirect('Error preparing query for updating item status.', 'admin.php');
            }
            $stmtItem->bind_param("i", $reportId);
            if (!$stmtItem->execute()) {
                alertAndRedirect('Error updating item status.', 'admin.php');
            }
        } elseif ($action == 'reject') {
            $sqlUpdate = "UPDATE reports SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($sqlUpdate);
            if ($stmt === false) {
                alertAndRedirect('Error preparing query for report rejection.', 'admin.php');
            }
            $stmt->bind_param("i", $reportId);
            if (!$stmt->execute()) {
                alertAndRedirect('Error executing report rejection.', 'admin.php');
            }
        }

        alertAndRedirect('Action completed. Redirecting...', 'admin.php');
    }
}

$conn->close();
?>
