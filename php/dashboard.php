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
    // Fetch Lost Items
    $stmtLost = $conn->prepare("SELECT * FROM items WHERE item_type = 'Lost' AND visibility = 'visible' ORDER BY date_reported DESC LIMIT 10");
    $stmtLost->execute();
    $lostItems = $stmtLost->get_result();

    // Fetch Found Items
    $stmtFound = $conn->prepare("SELECT * FROM items WHERE item_type = 'Found' AND visibility = 'visible' ORDER BY date_reported DESC LIMIT 10");
    $stmtFound->execute();
    $foundItems = $stmtFound->get_result();

    // Fetch For You Items
    $stmtForYou = $conn->prepare("SELECT * FROM items WHERE visibility = 'visible' ORDER BY RAND() LIMIT 10");
    $stmtForYou->execute();
    $forYouItems = $stmtForYou->get_result();

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
    <?php include 'navbar.php'; ?>
</header>

<main>
    <section>
        <h2>For You</h2>
        <div class="carousel-container">
            <?php if ($forYouItems->num_rows > 0): ?>
                <?php while ($item = $forYouItems->fetch_assoc()): ?>
                    <div class="carousel-item">
                        <img src="<?= htmlspecialchars(!empty($item['image_path']) ? $item['image_path'] : '../images/default_image.jpg') ?>" alt="Item Image">
                        <div class="card-content">
                            <h3><?= htmlspecialchars($item['item_name']); ?></h3><br>
                            <?= htmlspecialchars($item['item_description']) ?>
                            <p>Location: <?= htmlspecialchars($item['location']) ?></p>
                            <a href="view_report.php?id=<?= $item['id'] ?>" class="button">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No items available for you.</p>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <h2>Lost Items</h2>
        <div class="carousel-container">
            <?php while ($item = $lostItems->fetch_assoc()): ?>
                <div class="carousel-item">
                    <img src="<?= htmlspecialchars(!empty($item['image_path']) ? $item['image_path'] : '../images/default_image.jpg') ?>" alt="Item Image">
                    <div class="card-content">
                    <h3><?= htmlspecialchars($item['item_name']); ?></h3><br>
                       <?= htmlspecialchars($item['item_description']) ?>
                        <p>Location: <?= htmlspecialchars($item['location']) ?></p>
                        <a href="view_report.php?id=<?= $item['id'] ?>" class="button">View Details</a><br><br>

                        <!-- Report as Found Button -->
                        <div class="found-btn-container">
                            <button type="button" class="found-btn" data-item-id="<?= $item['id'] ?>" data-item-name="<?= htmlspecialchars($item['item_name']) ?>" data-item-description="<?= htmlspecialchars($item['item_description']) ?>">Report as Found</button>
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
                    <img src="<?= htmlspecialchars(!empty($item['image_path']) ? $item['image_path'] : '../images/default_image.jpg') ?>" alt="Item Image">
                    <div class="card-content">
                    <h3><?= htmlspecialchars($item['item_name']); ?></h3><br>
                    <?= htmlspecialchars($item['item_description']) ?>
                        <p>Location: <?= htmlspecialchars($item['location']) ?></p>
                        <a href="view_report.php?id=<?= $item['id'] ?>" class="button">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Modal Overlay for Reporting as Found -->
    <div id="modal-overlay" class="modal-overlay">
        <div class="modal-form">
            <button type="button" class="close-modal-btn">X</button>

            <!-- Form to Report as Found -->
            <form method="POST" action="report_found.php" enctype="multipart/form-data" id="found-form">
                <input type="hidden" name="lost_item_id" id="lost-item-id">
                <input type="hidden" name="lost_item_name" id="lost-item-name">
                <input type="hidden" name="lost_item_description" id="lost-item-description">

                <label for="found_image">Upload a Photo (Optional):</label>
                <input type="file" name="found_image" id="found_image"><br><br>

                <label for="location">Found Location:</label><br>
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
            </select>       <br>     
                <label for="found_description">Description of the Found Item:</label>
                <textarea name="found_description" id="found_description" rows="4" cols="50" required></textarea><br><br>

                <button type="submit" name="report_found" class="submit-found-btn">Submit Report</button>
            </form>
        </div>
    </div>
</main>

<footer>
    <p>© <?= date("Y") ?> Lost and Seek | All Rights Reserved</p>
</footer>

<script>
    document.querySelectorAll(".found-btn").forEach(button => {
        button.addEventListener("click", function () {
            let modal = document.getElementById("modal-overlay");

            // Get item details from the button's data attributes
            let itemId = this.getAttribute("data-item-id");
            let itemName = this.getAttribute("data-item-name");
            let itemDescription = this.getAttribute("data-item-description");

            // Set values in the modal form
            document.getElementById("lost-item-id").value = itemId;
            document.getElementById("lost-item-name").value = itemName;
            document.getElementById("lost-item-description").value = itemDescription;

            modal.style.display = "flex";  // Show the modal
        });
    });

    document.querySelector(".close-modal-btn").addEventListener("click", function () {
        document.getElementById("modal-overlay").style.display = "none"; // Hide modal
    });

    // Close modal if user clicks outside of it
    document.getElementById("modal-overlay").addEventListener("click", function (event) {
        if (event.target === this) {
            this.style.display = "none";
        }
    });
</script>

</body>
</html>