<?php
session_start();
include 'connect.php'; 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM items WHERE user_id = ?"; 
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
    <link rel="stylesheet" href="../css/myreports.css">
    <title>My Reports - Lost and Seek</title>
</head>
<body>

<header>
    <?php include 'navbar.php'; ?>
</header>

<main>
    <section id="report-summary">
        <h2>Your Submitted Reports</h2>
        <div id="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($report = $result->fetch_assoc()) {
                        $formattedDate = date('F j, Y', strtotime($report['date_reported']));
                        
                        // Sanitize input and apply class based on status
                        $status = htmlspecialchars($report['item_type']);
                        $statusClass = strtolower($status) == 'found' ? 'status-found' : 'status-lost';

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($report['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($report['item_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($report['item_description']) . "</td>";
                        echo "<td class='$statusClass'>" . $status . "</td>";
                        echo "<td>" . $formattedDate . "</td>";
                        echo "<td>";
                        echo "<a href='view_report.php?id=" . htmlspecialchars($report['id']) . "'>View</a> | ";
                        echo "<a href='delete_report.php?id=" . htmlspecialchars($report['id']) . "' onclick='return confirm(\"Are you sure you want to delete this report?\");'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No reports found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </div>

        <!-- Pagination (Placeholder for future implementation) -->
        <div class="pagination">
            <a href="#">« Prev</a>
            <a href="#">Next »</a>
        </div>

    </section>
</main>

<footer>
    <p>© <?= date("Y") ?> Lost and Seek | All Rights Reserved</p>
</footer>

</body>
</html>
