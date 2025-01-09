<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$sqlUser = "SELECT * FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$userResult = $stmtUser->get_result();
$user = $userResult->fetch_assoc();

try {
    $stmtLost = $conn->prepare("SELECT * FROM items WHERE item_type = 'Lost' AND visibility = 'visible' ORDER BY date_reported DESC LIMIT 10");
    $stmtLost->execute();
    $lostItems = $stmtLost->get_result();

    $stmtFound = $conn->prepare("SELECT * FROM items WHERE item_type = 'Found' AND status = 'found' AND visibility = 'visible' ORDER BY date_reported DESC LIMIT 10");
    $stmtFound->execute();
    $foundItems = $stmtFound->get_result();

    $stmtForYou = $conn->prepare("SELECT * FROM items WHERE visibility = 'visible' ORDER BY RAND() LIMIT 10");
    $stmtForYou->execute();
    $forYouItems = $stmtForYou->get_result();

    if ($forYouItems->num_rows == 0) {
        throw new Exception("Error fetching 'For You' items.");
    }
} catch (Exception $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Dashboard-Lost and Seek</title>
</head>
<body>

<header>
    <h1>Lost & Seek Dashboard</h1>
    <?php include 'navbar.php'; ?>
</header>

    <main>
        <section>
            <h2>For You</h2>
            <div class="carousel-container">
                <?php while ($item = $forYouItems->fetch_assoc()): ?>
                    <div class="carousel-item">
                        <img src="<?= htmlspecialchars(!empty($item['image_path']) ? $item['image_path'] : '../images/default_image.png') ?>" alt="Item Image">
                        <div class="card-content">
                            <h3> <?= htmlspecialchars($item['item_name']); ?></h3>
                            <p><?= htmlspecialchars($item['item_description']) ?></p>
                            <p>Location: <?= htmlspecialchars($item['location']) ?></p>
                            <a href="view_report.php?id=<?= $item['id'] ?>" class="button">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section>
    <h2>Lost Items</h2>
    <div class="carousel-container">
        <?php while ($item = $lostItems->fetch_assoc()): ?>
            <div class="carousel-item">
                <img src="<?= htmlspecialchars(!empty($item['image_path']) ? $item['image_path'] : '../images/default_image.png') ?>" alt="Item Image">
                <div class="card-content">
                    <h3><?= htmlspecialchars($item['item_description']) ?></h3>
                    <p>Location: <?= htmlspecialchars($item['location']) ?></p>
                    <a href="view_report.php?id=<?= $item['id'] ?>" class="button">View Details</a><br><br>
                    
                    <!-- Report as Found Button -->
                    <div class="found-btn-container">
                        <button type="button" class="found-btn" onclick="toggleForm(<?= $item['id'] ?>)">Report as Found</button>

                        <!-- Modal Overlay for Reporting as Found -->
                        <div id="modal-overlay-<?= $item['id'] ?>" class="modal-overlay">
                            <div class="modal-form">
                                <button type="button" class="close-modal-btn" onclick="toggleForm(<?= $item['id'] ?>)">X</button>
                                
                                <!-- Form to Report as Found -->
                                <form method="POST" action="report_found.php" enctype="multipart/form-data" id="found-form-<?= $item['id'] ?>">
                                    <input type="hidden" name="lost_item_id" value="<?= htmlspecialchars($item['id']) ?>">
                                    <input type="hidden" name="lost_item_name" value="<?= htmlspecialchars($item['item_name']) ?>">
                                    <input type="hidden" name="lost_item_description" value="<?= htmlspecialchars($item['item_description']) ?>">

                                    <label for="found_image">Upload a Photo (Optional):</label>
                                    <input type="file" name="found_image" id="found_image"><br><br>

                                    <label for="found_description">Description of the Found Item:</label>
                                    <textarea name="found_description" id="found_description" rows="4" cols="50" required></textarea><br><br>

                                    <button type="submit" name="report_found" class="found-btn">Submit Report</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>


        <section>
            <h2>Found Items</h2>
            <div class="carousel-container">
                <?php while ($item = $foundItems->fetch_assoc()): ?>
                    <div class="carousel-item">
                        <img src="<?= htmlspecialchars(!empty($item['image_path']) ? $item['image_path'] : '../images/default_image.png') ?>" alt="Item Image">
                        <div class="card-content">
                            <h3><?= htmlspecialchars($item['item_description']) ?></h3>
                            <p>Location: <?= htmlspecialchars($item['location']) ?></p>
                            <a href="view_report.php?id=<?= $item['id'] ?>" class="button">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </main>

<footer>
    <p>© <?= date("Y") ?> Lost and Seek | All Rights Reserved</p>
</footer>
<script>
    function toggleForm(itemId) {
    var modal = document.getElementById('modal-overlay-' + itemId); // Modal overlay
    var form = document.getElementById('found-form-' + itemId); // The form inside the modal

    // Toggle the modal visibility
    if (modal.style.display === "none" || modal.style.display === "") {
        modal.style.display = "flex";  // Show the modal
    } else {
        modal.style.display = "none";  // Hide the modal
    }
}


</script>

</body>
</html>
