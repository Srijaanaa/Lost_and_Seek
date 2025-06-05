<?php
session_start();

include 'connect.php';

$message = ''; // Variable to store success or error message

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to report an item.";
    exit;
}

$user_id = $_SESSION['user_id'];
$contact_info = null; // Default to null

// Fetch phone number from the users table
$sql = "SELECT phone_number FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $contact_info = $row['phone_number']; // Assign phone number to contact_info
}
$stmt->close();

$item_type = $item_name = $item_description = $location = $image_path = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_type = htmlspecialchars($_POST['item_type']);
    $item_name = htmlspecialchars($_POST['item_name']);
    $item_description = htmlspecialchars($_POST['item_description']);
    $location = htmlspecialchars($_POST['location']);

    if (!empty($_FILES['item_image']['name'])) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["item_image"]["name"]);

        // Validate file size (limit to 5MB)
        if ($_FILES["item_image"]["size"] > 5000000) {
            $message = "Sorry, your file is too large.";
        } else {
            $allowed_types = ["jpg", "jpeg", "png", "gif"];
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if (!in_array($file_type, $allowed_types)) {
                $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                } else {
                    $message = "Sorry, there was an error uploading your file.";
                }
            }
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO items (user_id, item_type, item_name, item_description, location, contact_info, image_path) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $item_type, $item_name, $item_description, $location, $contact_info, $image_path);

        if ($stmt->execute()) {
            $message = "New item reported successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    // $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit-Lost and Seek</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
    <?php include 'navbar.php'; ?>
    </header>

    <main id="container">
        <h2>Report a Lost or Found Item</h2>

        <form action="submit_item.php" method="POST" id="myform" enctype="multipart/form-data">
            <label for="itemType">Item Type:</label>
            <select id="itemType" name="item_type" required>
                <option value="lost">Lost</option>
                <option value="found">Found</option>
            </select>

            <label for="itemName">Item Name:</label>
            <input type="text" id="itemName" name="item_name" required>

            <label for="itemDesc">Item Description:</label>
            <textarea id="itemDesc" name="item_description" required></textarea>

            <label for="location">Location (Kathmandu):</label>
            <select id="location" name="location" required>
                <option value="Kalanki">Kalanki</option>
                <option value="Thamel">Thamel</option>
                <option value="Durbar Square">Durbar Square</option>
                <option value="Patan Durbar Square">Patan Durbar Square</option>
                <option value="Bhaktapur Durbar Square">Bhaktapur Durbar Square</option>
                <option value="Swayambhunath">Swayambhunath</option>
                <option value="Boudhanath Stupa">Boudhanath Stupa</option>
                <option value="Nagarkot">Nagarkot</option>
                <option value="Pashupatinath Temple">Pashupatinath Temple</option>
                <option value="Garden of Dreams">Garden of Dreams</option>
                <option value="Lalitpur">Lalitpur</option>
                <option value="Kirtipur">Kirtipur</option>
            </select>

            <label for="image">Item Image (optional):</label>
            <input type="file" id="image" name="item_image" accept=".jpg, .jpeg, .png, .gif">

            <input type="submit" id="submit" value="Submit">
        </form>

        <a href="dashboard.php" class="go-back">Back to Dashboard</a>

        <?php if ($message != ''): ?>
            <div class="message-box">
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Lost and Seek</p>
    </footer>
</body>
</html>
