<?php
session_start();
include 'connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

function safeDisplay($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Lost and Seek</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <header>
        <h1>Admin Panel - Lost and Seek</h1>
        <?php include 'navbar.php'; ?>
    </header>

    <main>
        <section id="notifications">
            <h2>Notifications</h2>
            <div class="notifications-container">
                <?php
                $sqlNotif = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0";
                $stmtNotif = $conn->prepare($sqlNotif);
                $stmtNotif->bind_param("i", $_SESSION['user_id']);
                $stmtNotif->execute();
                $notifResult = $stmtNotif->get_result();
                if ($notifResult->num_rows > 0) {
                    while ($notif = $notifResult->fetch_assoc()) {
                        echo "<p>" . safeDisplay($notif['message']) . "</p>";
                    }
                } else {
                    echo "<p>No new notifications.</p>";
                }
                ?>
            </div>
    
        </section>
        <section id="user-management">
            <h2>Manage Users</h2>
            <form method="GET" action="admin.php" style="margin-bottom: 20px;">
                <input type="text" name="search" placeholder="Search users by name or email" value="<?= isset($_GET['search']) ? safeDisplay($_GET['search']) : '' ?>">
                <button type="submit">Search</button>
            </form>
            <div class="user-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $limit = 10;
                        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
                        $offset = ($page - 1) * $limit;

                        $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%%";
                        $sql = "SELECT id, username, email, role FROM users WHERE username LIKE ? OR email LIKE ? LIMIT ? OFFSET ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssii", $search, $search, $limit, $offset);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . safeDisplay($row['id']) . "</td>";
                                echo "<td>" . safeDisplay($row['username']) . "</td>";
                                echo "<td>" . safeDisplay($row['email']) . "</td>";
                                echo "<td>";
                                echo "<form action='change_role.php' method='POST'>"; 
                                echo "<input type='hidden' name='user_id' value='" . safeDisplay($row['id']) . "'>";
                                echo "<select name='role' onchange='this.form.submit()'>";
                                echo "<option value='user'" . ($row['role'] == 'user' ? " selected" : "") . ">User</option>";
                                echo "<option value='admin'" . ($row['role'] == 'admin' ? " selected" : "") . ">Admin</option>";
                                echo "</select>";
                                echo "</form>";
                                echo "</td>";
                                echo "<td>";
                                echo "<form action='delete_user.php' method='POST' style='display:inline;'>";
                                echo "<input type='hidden' name='user_id' value='" . safeDisplay($row['id']) . "'>";
                                echo "<input type='submit' value='Delete'>";
                                echo "</form>";
                                echo "<a href='user_details.php?user_id=" . safeDisplay($row['id']) . "'>View Details</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <?php
                $sqlTotal = "SELECT COUNT(*) FROM users WHERE username LIKE ? OR email LIKE ?";
                $stmtTotal = $conn->prepare($sqlTotal);
                $stmtTotal->bind_param("ss", $search, $search);
                $stmtTotal->execute();
                $resultTotal = $stmtTotal->get_result();
                $total = $resultTotal->fetch_row()[0];
                $totalPages = ceil($total / $limit);

                for ($i = 1; $i <= $totalPages; $i++) {
                    echo "<a href='?page=$i&search=" . (isset($_GET['search']) ? safeDisplay($_GET['search']) : '') . "'>$i</a> ";
                }
                ?>
            </div>
        </section>
        <section id="manage-reports">
            <h2>Manage Reported Items</h2>

            <h3>Reported Lost Items</h3>
            <div class="item-card-container">
                <?php
                $sql = "SELECT * FROM items WHERE item_type = 'Lost' AND visibility = 'visible'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $lost_item_id = $row['id']; 
                        echo "<div class='item-card'>";
                        echo "<h4>" . safeDisplay($row['item_name']) . "</h4>";
                        echo "<p><strong>Item ID:</strong> " . safeDisplay($row['id']) . "</p>";  
                        echo "<p>" . safeDisplay($row['item_description']) . "</p>";
                        echo "<p><strong>Location:</strong> " . safeDisplay($row['location']) . "</p>";
                        echo "<p><strong>Contact:</strong> " . safeDisplay($row['contact_info']) . "</p>";
                        echo "<div class='button-row'>";
                        echo "<button class='match-button' onclick='showMatchForm()'>Match</button>";
                        echo "<form action='confirm_action.php' method='POST'>";
                        echo "<input type='hidden' name='item_id' value='" . safeDisplay($row['id']) . "'>";
                        echo "<button type='submit' name='action' value='delete'>Delete</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No lost items.</p>";
                }
                ?>
            </div>
            <div id="overlay"></div>
            <div id="match-form">
                <h2>Match Items</h2>
                <form action="match_items.php" id="myform" method="POST">
                    <label for="lost_item_id">Lost Item ID:</label>
                    <input type="text" name="lost_item_id" id="lost_item_id" placeholder="Enter Lost Item ID" required><br>
                    
                    <label for="found_item_id">Found Item ID:</label>
                    <input type="text" name="found_item_id" id="found_item_id" placeholder="Enter Found Item ID" required><br>
                    
                    <button type="submit">Confirm Match</button>
                    <button type="button" onclick="hideMatchForm()">Cancel</button>
                </form>
            </div>

            <h3>Reported Found Items</h3>
            <div class="item-card-container">
                <?php
                $sql = "SELECT * FROM items WHERE item_type = 'Found' AND visibility = 'visible'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $found_item_id = $row['id']; 
                        echo "<div class='item-card'>";
                        echo "<h4>" . safeDisplay($row['item_name']) . "</h4>";
                        echo "<p><strong>Item ID:</strong> " . safeDisplay($row['id']) . "</p>"; 
                        echo "<p>" . safeDisplay($row['item_description']) . "</p>";
                        echo "<p><strong>Location:</strong> " . safeDisplay($row['location']) . "</p>";
                        echo "<p><strong>Contact:</strong> " . safeDisplay($row['contact_info']) . "</p>";
                        echo "<div class='button-row'>";
                        echo "<button class='match-button' onclick='showMatchForm()'>Match</button>";
                        echo "<form action='confirm_action.php' method='POST'>";
                        echo "<input type='hidden' name='item_id' value='" . safeDisplay($row['id']) . "'>";
                        echo "<button type='submit' name='action' value='delete'>Delete</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No pending found items.</p>";
                }
                ?>
            </div>
        </section>
        <section id="matched-items">
    <h2>Matched Items</h2>
    <div class="item-card-container">
        <?php
        $sqlMatched = "
            SELECT mi.*, li.item_name AS lost_item_name, fi.item_name AS found_item_name
            FROM matched_items mi
            JOIN items li ON mi.lost_item_id = li.id
            JOIN items fi ON mi.found_item_id = fi.id
            WHERE mi.match_status = 'approved'
        ";
        $resultMatched = $conn->query($sqlMatched);
        if ($resultMatched->num_rows > 0) {
            while ($row = $resultMatched->fetch_assoc()) {
                echo "<div class='item-card'>";
                echo "<h4>" . safeDisplay($row['lost_item_name']) . " & " . safeDisplay($row['found_item_name']) . " Matched</h4>";
                echo "<p><strong>Lost Item ID:</strong> " . safeDisplay($row['lost_item_id']) . "</p>";
                echo "<p><strong>Found Item ID:</strong> " . safeDisplay($row['found_item_id']) . "</p>";
                echo "<p><strong>Status:</strong> Matched and Approved</p>";
                echo "</div>";
            }
        } else {
            echo "<h4>No matched items.</h4>";
        }
        ?>
    </div>
</section>

<section id="requested-matched-items">
    <h2>Requested Matched Items</h2>
    <div class="item-card-container">
        <?php
        $sqlRequested = "
            SELECT mi.*, li.item_name AS lost_item_name, fi.item_name AS found_item_name
            FROM matched_items mi
            JOIN items li ON mi.lost_item_id = li.id
            JOIN items fi ON mi.found_item_id = fi.id
            WHERE mi.match_status = 'pending'
        ";
        $resultRequested = $conn->query($sqlRequested);
        if ($resultRequested->num_rows > 0) {
            while ($row = $resultRequested->fetch_assoc()) {
                echo "<div class='item-card'>";
                echo "<h4>" . safeDisplay($row['lost_item_name']) . " & " . safeDisplay($row['found_item_name']) . " Requested</h4>";
                echo "<p><strong>Lost Item ID:</strong> " . safeDisplay($row['lost_item_id']) . "</p>";
                echo "<p><strong>Found Item ID:</strong> " . safeDisplay($row['found_item_id']) . "</p>";
                echo "<p><strong>Status:</strong> Requested, Pending Confirmation</p>";
                echo "<form action='confirm_action.php' method='POST'>"; 
                echo "<input type='hidden' name='matched_item_id' value='" . safeDisplay($row['id']) . "'>";
                echo "<button type='submit' name='action' value='approve'>Approve Match</button>";
                echo "</form>";
                echo "<form action='confirm_action.php' method='POST'>"; 
                echo "<input type='hidden' name='matched_item_id' value='" . safeDisplay($row['id']) . "'>";
                echo "<button type='submit' name='action' value='reject'>Reject Match</button>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<p>No requested matched items.</p>";
        }
        ?>
    </div>
</section>


        <section id="deleted-items">
            <h2>Deleted Items</h2>
            <div class="item-card-container">
                <?php
                $sqlDeleted = "SELECT * FROM items WHERE visibility = 'hidden'";
                $resultDeleted = $conn->query($sqlDeleted);
                if ($resultDeleted->num_rows > 0) {
                    while ($row = $resultDeleted->fetch_assoc()) {
                        echo "<div class='item-card'>";
                        echo "<h4>" . safeDisplay($row['item_name']) . "</h4>";
                        echo "<p><strong>Item ID:</strong> " . safeDisplay($row['id']) . "</p>";  
                        echo "<p>" . safeDisplay($row['item_description']) . "</p>";
                        echo "<p><strong>Status:</strong>" . safeDisplay($row['status']) . "</p>";
                        echo "<p><strong>Location:</strong> " . safeDisplay($row['location']) . "</p>";
                        echo "<p><strong>Contact:</strong> " . safeDisplay($row['contact_info']) . "</p>";
                        echo "<div class='button-row'>";
                        echo "<form action='confirm_action.php' method='POST'>";
                        echo "<input type='hidden' name='item_id' value='" . safeDisplay($row['id']) . "'>";
                        echo "<button type='submit' name='action' value='restore'>Restore</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No deleted items.</p>";
                }
                ?>
            </div>
        </section>
        
    </main>

    <footer>
        <p>&copy; 2025 Lost and Seek. All rights reserved.</p>
    </footer>

    <script>
        function showMatchForm() {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('match-form').style.display = 'block';
        }

        function hideMatchForm() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('match-form').style.display = 'none';
        }
    </script>
</body>
</html>
