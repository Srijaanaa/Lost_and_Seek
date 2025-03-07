<?php
session_start();
include 'connect.php'; // Include your database connection file

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Ensure that an item ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    die("Item ID not provided.");
}

$item_id = $_POST['id']; // Get the item ID from the URL

// Check if the database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update the item's status to 'Found'
$sql = "UPDATE items SET item_type = 'Found' WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $item_id);

// Execute the statement and check for errors
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Item confirmed as found successfully!";

        // Optionally, add logic to insert a match into the 'matched_items' table
        $match_sql = "INSERT INTO matched_items (lost_item_id, found_item_id) VALUES (?, ?)";
        $match_stmt = $conn->prepare($match_sql);

        if ($match_stmt === false) {
            die("Error preparing match statement: " . $conn->error);
        }

        // Assuming you have the necessary data to link the lost and found items
        // Bind parameters for the match (you may need to adjust this depending on your table structure)
        $match_stmt->bind_param("ii", $lost_item_id, $item_id); // Assuming $lost_item_id is available

        if ($match_stmt->execute()) {
            echo "Matched items linked successfully!";
        } else {
            echo "Error linking matched items.";
        }
    } else {
        echo "No changes were made to the item status.";
    }
} else {
    echo "Error updating the item status.";
}

$stmt->close();
$conn->close();
?>
