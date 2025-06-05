<?php
include 'connect.php';

if (isset($_GET['query'])) {
    $query = $conn->real_escape_string($_GET['query']); 

    $sql = "SELECT * FROM items WHERE item_name LIKE ? OR item_description LIKE ? OR location LIKE ?";
    $stmt = $conn->prepare($sql);

    $searchTerm = "%$query%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);

    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search-Lost and Seek</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <?php include 'navbar.php'; ?>
    </header>
    
    <main id="container">
        <h2>Search Lost/Found Items</h2>
        <form id="myform" method="GET">
            <input type="text" id="searchQuery" name="query" placeholder="Search for lost or found items..." value="<?= htmlspecialchars($_GET['query'] ?? '') ?>" required>
            <button type="submit">Search</button>
        </form>
        
        <div id="searchResults">
            <?php
            if (isset($result)) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='item-card'>";
                        echo "<h3>" . htmlspecialchars($row['item_name']) . "</h3>";
                        echo "<p><strong>Status: </strong>" . htmlspecialchars($row['item_type']) . "</p>"; 
                        echo "<p>" . htmlspecialchars($row['item_description']) . "</p>";
                        echo "<p><strong>Location: </strong>" . htmlspecialchars($row['location']) . "</p>";
                        if (!empty($row['image_path'])) {
                            echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Item Image' class='item-image'>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<p>No items found.</p>";
                }
            }
            ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Lost and Seek</p>
    </footer>

</body>
</html>

<?php
$conn->close();
?>
