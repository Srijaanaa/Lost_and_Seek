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
$found_description = htmlspecialchars($_POST['found_description'], ENT_QUOTES, 'UTF-8');
$location = htmlspecialchars($_POST['location'], ENT_QUOTES, 'UTF-8');
$found_image_path = '';

// Fetch the lost item's name from the database
$sqlLostItem = "SELECT item_name FROM items WHERE id = ?";
$stmtLostItem = $conn->prepare($sqlLostItem);
$stmtLostItem->bind_param("i", $lost_item_id);
$stmtLostItem->execute();
$resultLostItem = $stmtLostItem->get_result();

if ($resultLostItem->num_rows > 0) {
    $rowLostItem = $resultLostItem->fetch_assoc();
    $found_item_name = $rowLostItem['item_name'];
} else {
    echo "<script>alert('Lost item not found.'); window.location.href = 'dashboard.php';</script>";
    exit();
}

// Fetch the user's contact information from the database
$sqlUser = "SELECT phone_number FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $_SESSION['user_id']);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($resultUser->num_rows > 0) {
    $rowUser = $resultUser->fetch_assoc();
    $contact_info = $rowUser['phone_number'];
} else {
    echo "<script>alert('User not found.'); window.location.href = 'dashboard.php';</script>";
    exit();
}

if (!empty($_FILES['found_image']['name'])) {
    $target_dir = "../images/";
    $target_file = $target_dir . basename($_FILES["found_image"]["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ["jpg", "jpeg", "png", "gif"];

    if ($_FILES["found_image"]["size"] > 5000000) {
        echo "<script>alert('Sorry, your file is too large.'); window.location.href = 'dashboard.php';</script>";
        exit();
    } elseif (!in_array($file_type, $allowed_types)) {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.'); window.location.href = 'dashboard.php';</script>";
        exit();
    } elseif (move_uploaded_file($_FILES["found_image"]["tmp_name"], $target_file)) {
        $found_image_path = $target_file;
    } else {
        echo "<script>alert('Sorry, there was an error uploading your file.'); window.location.href = 'dashboard.php';</script>";
        exit();
    }
}

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
        // Insert found item into items table with item_type 'found' and status 'pending'
        $sqlFoundItem = "INSERT INTO items (user_id, item_type, item_name, item_description, location, contact_info, image_path, status) VALUES (?, 'found', ?, ?, ?, ?, ?, 'pending')";
        $stmtFoundItem = $conn->prepare($sqlFoundItem);
        $stmtFoundItem->bind_param("isssss", $_SESSION['user_id'], $found_item_name, $found_description, $location, $contact_info, $found_image_path);

        if ($stmtFoundItem->execute()) {
            $found_item_id = $stmtFoundItem->insert_id;

            // Insert into matched_items table
            $sqlMatched = "INSERT INTO matched_items (lost_item_id, found_item_id, admin_id, resolution_details, match_status) VALUES (?, ?, ?, ?, 'pending')";
            $stmtMatched = $conn->prepare($sqlMatched);
            $stmtMatched->bind_param("iiis", $lost_item_id, $found_item_id, $adminId, $found_description);

            if ($stmtMatched->execute()) {
                echo "<script>alert('Item reported as found. Awaiting admin confirmation.'); window.location.href = 'dashboard.php';</script>";
            } else {
                echo "<script>alert('Error inserting matched item.'); window.location.href = 'dashboard.php';</script>";
            }

            $stmtMatched->close();
        } else {
            echo "<script>alert('Error inserting found item.'); window.location.href = 'dashboard.php';</script>";
        }

        $stmtFoundItem->close();
    } else {
        echo "<script>alert('Error creating notification.'); window.location.href = 'dashboard.php';</script>";
    }

    $stmtNotif->close();
} else {
    echo "<script>alert('No admin found. Cannot send notification.'); window.location.href = 'dashboard.php';</script>";
}

$conn->close();
?>