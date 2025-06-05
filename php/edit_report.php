<?php
session_start();
include 'connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $report_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM items WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $report_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $report = $result->fetch_assoc();
    } else {
        die("Report not found or you do not have permission to edit this report.");
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $report_id = $_POST['id'];
    $user_id = $_SESSION['user_id'];
    $item_name = trim($_POST['item_name']);
    $item_description = trim($_POST['item_description']);
    $item_type = trim($_POST['item_type']);

    $sql = "UPDATE items SET item_name = ?, item_description = ?, item_type = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $item_name, $item_description, $item_type, $report_id, $user_id);

    if ($stmt->execute()) {
        header("Location: myreports.php");
        exit();
    } else {
        die("Something went wrong. Please try again.");
    }
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Edit Report - Lost and Seek</title>
</head>
<body>

<header>
    <?php include 'navbar.php'; ?>
</header>

<main  id="container">
        <h2>Edit Report</h2>
        <form action="edit_report.php" method="POST" id="myform">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($report['id']); ?>">

            <label for="item_name">Item Name:</label>
            <input type="text" id="item_name" name="item_name" required value="<?php echo htmlspecialchars($report['item_name']); ?>"><br>

            <label for="item_description">Item Description:</label>
            <textarea id="item_description" name="item_description" required><?php echo htmlspecialchars($report['item_description']); ?></textarea><br>

            <label for="item_type">Status:</label>
    <select id="item_type" name="item_type" required>
        <option value="lost" <?php if ($report['item_type'] == 'lost') echo 'selected'; ?> 
            <?php if ($report['item_type'] == 'found') echo 'disabled'; ?>>Lost</option>
        <option value="found" <?php if ($report['item_type'] == 'found') echo 'selected'; ?>>Found</option>
    </select><br>

            <button type="submit">Update Report</button>
        </form>
        <a href="myreports.php" class="go-back">Back</a>

</main>

<footer>
    <p>© <?= date("Y") ?> Lost and Seek | All Rights Reserved</p>
</footer>

</body>
</html>