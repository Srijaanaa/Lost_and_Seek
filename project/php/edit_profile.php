<?php
session_start();
include 'connect.php'; 

// Redirect if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$userId = $_SESSION['user_id'];
$sqlUser = "SELECT * FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$userResult = $stmtUser->get_result();
$user = $userResult->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    $phone_number = $_POST['phone_number'];  // Ensure this retains the '+' character

    $sqlUpdate = "UPDATE users SET username = ?, email = ?, full_name = ?, phone_number = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssssi", $name, $email, $full_name, $phone_number, $userId);
    
    if ($stmtUpdate->execute()) {
        $message = "Profile updated successfully!";
        // Refresh user data after update
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();
        $user = $userResult->fetch_assoc();
    } else {
        $message = "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Edit Profile - Lost and Seek</title>
</head>
<body>

<header>
    <?php include 'navbar.php'; ?>
</header>

<main id="container">
    <h2>Edit Profile</h2>
    <form method="POST" action="edit_profile.php" id="myform">
        <label for="name">Username:</label>
        <input type="text" id="name" name="name" value="<?= isset($name) ? htmlspecialchars($name) : htmlspecialchars($user['username']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : htmlspecialchars($user['email']) ?>" required>

        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" value="<?= isset($full_name) ? htmlspecialchars($full_name) : htmlspecialchars($user['full_name']) ?>" required>

        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number" value="<?= isset($phone_number) ? htmlspecialchars($phone_number) : htmlspecialchars($user['phone_number']) ?>" required>

        <button type="submit">Update Profile</button>
    </form>
    <a href="dashboard.php" class="go-back">Back to Dashboard</a>

    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
</main>

<footer>
    <p>© <?= date("Y") ?> Lost and Seek | All Rights Reserved</p>
</footer>

</body>
</html>
