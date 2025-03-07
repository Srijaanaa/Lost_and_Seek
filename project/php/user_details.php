<?php
session_start();
include 'connect.php';

function safeDisplay($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo "<script>alert('Invalid User ID.'); window.location.href = 'admin.php';</script>";
    exit();
}

$user_id = (int)$_GET['user_id'];

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>User_Details-Lost and Seek</title>
</head>
<body>
    <header>
        <?php include 'navbar.php'; ?>
    </header>
    <main id="container">
        <?php
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<h1>User Details</h1>";
            echo "<p><strong>ID:</strong> " . safeDisplay($user['id']) . "</p>";
            echo "<p><strong>Username:</strong> " . safeDisplay($user['username']) . "</p>";
            echo "<p><strong>Full Name:</strong> " . safeDisplay($user['full_name']) . "</p>";
            echo "<p><strong>Email:</strong> " . safeDisplay($user['email']) . "</p>";
            echo "<p><strong>Role:</strong> " . safeDisplay($user['role']) . "</p>";
        } else {
            echo "<p>User not found.</p><br>";
        }

        $stmt->close();
        $conn->close();
        ?>
        <a href="admin.php" class="go-back">Back to Admin Panel</a>
    </main>
    <footer>
        <p>&copy; <?= date("Y") ?> Lost and Seek</p>
    </footer>
</body>
</html>
