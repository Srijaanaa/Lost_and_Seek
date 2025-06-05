<?php
session_start();
include 'connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$is_admin = ($_SESSION['role'] == 'admin');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid report ID.";
    exit();
}

$report_id = (int)$_GET['id']; 

$sql = "SELECT * FROM items WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Error preparing statement.";
    exit();
}

$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $report = $result->fetch_assoc();
    
    $is_user_report = ($_SESSION['user_id'] == $report['user_id']);
} else {
    echo "Report not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>View_Report-Lost and Seek</title>
</head>
<body>

<header>
    <?php include 'navbar.php'; ?>
</header>

<main id="container">
    <?php if ($is_admin || $is_user_report): ?>
        <h2>Item Name: <?php echo htmlspecialchars($report['item_name']); ?></h2>
        <p><img src="<?= htmlspecialchars(!empty($report['image_path']) ? $report['image_path'] : '../images/default_image.jpg') ?>" alt="Item Image"></p>

        <p>Location: <?php echo htmlspecialchars($report['location']); ?></p>
        <p>Contact Info: <?php echo htmlspecialchars($report['contact_info']); ?></p>
        <p>Date Submitted: <?php echo htmlspecialchars($report['date_reported']); ?></p>
    <?php else: ?>
        <p>You do not have permission to view this report's details.</p>
    <?php endif; ?>
    <a href="dashboard.php" class="go-back">Back to Dashboard</a>

</main>

<footer>
    <p>© <?= date("Y") ?> Lost and Seek | All Rights Reserved</p>
</footer>

</body>
</html>
