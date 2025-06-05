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

            // Fetch the matched item details
            $sqlMatchedItem = "
                SELECT mi.*, li.user_id AS lost_user_id, fi.user_id AS found_user_id, li.contact_info AS lost_contact_info, fi.contact_info AS found_contact_info
                FROM matched_items mi
                JOIN items li ON mi.lost_item_id = li.id
                JOIN items fi ON mi.found_item_id = fi.id
                WHERE mi.id = ?
            ";
            $stmtMatchedItem = $conn->prepare($sqlMatchedItem);
            $stmtMatchedItem->bind_param("i", $matched_item_id);
            $stmtMatchedItem->execute();
            $resultMatchedItem = $stmtMatchedItem->get_result();

            if ($resultMatchedItem->num_rows > 0) {
                $rowMatchedItem = $resultMatchedItem->fetch_assoc();
                $lost_user_id = $rowMatchedItem['lost_user_id'];
                $found_user_id = $rowMatchedItem['found_user_id'];
                $lost_contact_info = $rowMatchedItem['lost_contact_info'];
                $found_contact_info = $rowMatchedItem['found_contact_info'];

                // Send notification to the lost item reporter
                $messageLost = "Your lost item has been matched. Contact info of the finder: $found_contact_info";
                $sqlNotificationLost = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
                $stmtNotificationLost = $conn->prepare($sqlNotificationLost);
                $stmtNotificationLost->bind_param("is", $lost_user_id, $messageLost);
                $stmtNotificationLost->execute();

                // Send notification to the found item reporter
                $messageFound = "Your found item has been matched. Contact info of the owner: $lost_contact_info";
                $sqlNotificationFound = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
                $stmtNotificationFound = $conn->prepare($sqlNotificationFound);
                $stmtNotificationFound->bind_param("is", $found_user_id, $messageFound);
                $stmtNotificationFound->execute();

                // Update the status of the items
                $sqlUpdateItems = "UPDATE items SET status = 'matched' WHERE id = ? OR id = ?";
                $stmtUpdateItems = $conn->prepare($sqlUpdateItems);
                $stmtUpdateItems->bind_param("ii", $rowMatchedItem['lost_item_id'], $rowMatchedItem['found_item_id']);
                $stmtUpdateItems->execute();
            } else {
                alertAndRedirect('Matched item not found.', 'admin.php');
            }
            break;
        case 'reject':
            $sqlRejectMatch = "UPDATE matched_items SET match_status = 'rejected' WHERE id = ?";
            executeQuery($conn, $sqlRejectMatch, ["i", $matched_item_id], 'Match rejected successfully.', 'Error rejecting match.');

            // Fetch the matched item details
            $sqlMatchedItem = "
                SELECT mi.*, li.user_id AS lost_user_id, fi.user_id AS found_user_id
                FROM matched_items mi
                JOIN items li ON mi.lost_item_id = li.id
                JOIN items fi ON mi.found_item_id = fi.id
                WHERE mi.id = ?
            ";
            $stmtMatchedItem = $conn->prepare($sqlMatchedItem);
            $stmtMatchedItem->bind_param("i", $matched_item_id);
            $stmtMatchedItem->execute();
            $resultMatchedItem = $stmtMatchedItem->get_result();

            if ($resultMatchedItem->num_rows > 0) {
                $rowMatchedItem = $resultMatchedItem->fetch_assoc();
                $lost_user_id = $rowMatchedItem['lost_user_id'];
                $found_user_id = $rowMatchedItem['found_user_id'];

                // Send notification to the lost item reporter
                $messageLost = "Your lost item match has been rejected.";
                $sqlNotificationLost = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
                $stmtNotificationLost = $conn->prepare($sqlNotificationLost);
                $stmtNotificationLost->bind_param("is", $lost_user_id, $messageLost);
                $stmtNotificationLost->execute();

                // Send notification to the found item reporter
                $messageFound = "Your found item match has been rejected.";
                $sqlNotificationFound = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
                $stmtNotificationFound = $conn->prepare($sqlNotificationFound);
                $stmtNotificationFound->bind_param("is", $found_user_id, $messageFound);
                $stmtNotificationFound->execute();
            } else {
                alertAndRedirect('Matched item not found.', 'admin.php');
            }
            break;
        default:
            alertAndRedirect('Invalid action for matched item.', 'admin.php');
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
}

// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     // Item-related actions
//     if (isset($_POST['item_id']) && isset($_POST['action'])) {
//         $item_id = (int)$_POST['item_id'];
//         $action = $_POST['action'];
//         handleItemAction($conn, $item_id, $action);
//     }

//     // Matched Item actions
//     elseif (isset($_POST['matched_item_id']) && isset($_POST['action'])) {
//         $matched_item_id = (int)$_POST['matched_item_id'];
//         $action = $_POST['action'];
//         handleMatchedItemAction($conn, $matched_item_id, $action);
//     }

//     // Match items
//     elseif (isset($_POST['lost_item_id']) && isset($_POST['found_item_id']) && $_POST['action'] == 'match') {
//         $lost_item_id = (int)$_POST['lost_item_id'];
//         $found_item_id = (int)$_POST['found_item_id'];

//         // Insert into matched_items
//         $sqlMatchItems = "INSERT INTO matched_items (lost_item_id, found_item_id, admin_id) VALUES (?, ?, ?)";
//         $stmtMatchItems = $conn->prepare($sqlMatchItems);
//         $stmtMatchItems->bind_param("iii", $lost_item_id, $found_item_id, $_SESSION['user_id']);
//         if ($stmtMatchItems->execute()) {
//             // Update the status of the items
//             $sqlUpdateItems = "UPDATE items SET status = 'matched', visibility = 'hidden' WHERE id = ? OR id = ?";
//             $stmtUpdateItems = $conn->prepare($sqlUpdateItems);
//             $stmtUpdateItems->bind_param("ii", $lost_item_id, $found_item_id);
//             $stmtUpdateItems->execute();

//             // Fetch user IDs for notifications
//             $sqlFetchUsers = "
//                 SELECT li.user_id AS lost_user_id, fi.user_id AS found_user_id
//                 FROM items li
//                 JOIN items fi ON fi.id = ?
//                 WHERE li.id = ?
//             ";
//             $stmtFetchUsers = $conn->prepare($sqlFetchUsers);
//             $stmtFetchUsers->bind_param("ii", $found_item_id, $lost_item_id);
//             $stmtFetchUsers->execute();
//             $resultFetchUsers = $stmtFetchUsers->get_result();
//             if ($resultFetchUsers->num_rows > 0) {
//                 $rowUsers = $resultFetchUsers->fetch_assoc();
//                 $lost_user_id = $rowUsers['lost_user_id'];
//                 $found_user_id = $rowUsers['found_user_id'];

//                 // Send notifications
//                 $messageLost = "Your lost item has been matched.";
//                 $sqlNotificationLost = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
//                 $stmtNotificationLost = $conn->prepare($sqlNotificationLost);
//                 $stmtNotificationLost->bind_param("is", $lost_user_id, $messageLost);
//                 $stmtNotificationLost->execute();

//                 $messageFound = "Your found item has been matched.";
//                 $sqlNotificationFound = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
//                 $stmtNotificationFound = $conn->prepare($sqlNotificationFound);
//                 $stmtNotificationFound->bind_param("is", $found_user_id, $messageFound);
//                 $stmtNotificationFound->execute();
//             }

//             alertAndRedirect('Items matched successfully.', 'admin.php');
//         } else {
//             alertAndRedirect('Error matching items.', 'admin.php');
//         }
//     }
// }

$conn->close();
?>
