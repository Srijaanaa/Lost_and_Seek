<?php
$userName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
}

nav {
    display: flex;
    justify-content: flex-end;
    background: linear-gradient(to right, #000080, #0056b3);
    padding: 10px 20px;
}

nav ul {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
}

nav ul li {
    margin: 20px;
}

nav ul li a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    transition: transform 0.3s ease, color 0.3s ease;
}

nav ul li a:hover {
    color: #ffcc00;
    transform: scale(1.1);

}

#profile-sidebar {
    position: fixed;
    top: 0;
    right: -20%;
    width: 20%;
    height: 100%;
    background: linear-gradient(to right, #000080, #0056b3);
    color: white;
    padding: 20px;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
    transition: right 0.3s ease, width 0.3s ease;
}

#profile-sidebar.open {
    right: 0;
}

.profile-content h3 {
    color: white;
    font-size: 24px; /* Increased size for the welcome text */
    margin-top: 50px;
}

.profile-content a {
    color: white;
    text-decoration: none;
    margin-top: 10px;
    display: inline-block;
    transition: color 0.3s ease;
}

.profile-content a:hover {
    color: #ffcc00;
}

.shifted {
    margin-right: 20%;
    transition: margin-right 0.3s ease;
}

#logo {
    margin-right: auto;
    display: flex;
    align-items: center;
}

#logo img {
    height: 60px;
    width: auto;
    object-fit: contain;
    transition: transform 0.3s ease; /* Smooth transition */

}
#logo img:hover {
    transform: scale(1.1);
}

#notifications {
    margin-top: 30px;
}

#notifications h4 {
    color: white;
    font-size: 18px;
    margin-bottom: 10px;
}

#notifications ul {
    list-style: none;
    padding-left: 0;
}

#notifications ul li {
    background-color: #333;
    padding: 10px;
    margin-bottom: 5px;
    border-radius: 5px;
    color: white;
    transition: background-color 0.3s ease;
}

#notifications ul li:hover {
    background-color: #ffcc00;
    color: black;
}

.profile-footer {
    margin-top: auto; /* Pushes the footer to the bottom */
    padding-top: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.profile-footer a {
    display: inline-block;
    padding: 10px 20px;
    margin-top: 10px;
    background-color: #ffcc00;
    color: black;
    text-decoration: none;
    font-weight: bold;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.profile-footer a:hover {
    background-color: #ff9900;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const profileSidebar = document.getElementById("profile-sidebar");
    const toggleProfile = document.getElementById("toggleProfile");
    const notificationsList = document.querySelector("#notifications ul");
    const noNotificationsMessage = document.createElement('li');
    
    noNotificationsMessage.textContent = "No new notifications"; // Default message when there are no notifications
    noNotificationsMessage.style.backgroundColor = "#444"; // Style for the "no notifications" message

    // Function to update the notification list
    function updateNotifications(notifications) {
        notificationsList.innerHTML = ''; // Clear any previous notifications

        if (notifications.length === 0) {
            notificationsList.appendChild(noNotificationsMessage); // Show "No new notifications" if the list is empty
        } else {
            notifications.forEach(notification => {
                const newNotification = document.createElement('li');
                newNotification.textContent = notification;
                notificationsList.appendChild(newNotification);
            });
        }
    }

    // Example of notifications array (replace with actual data)
    const notifications = []; // Empty array means no notifications

    updateNotifications(notifications);

    // Toggle profile sidebar
    if (toggleProfile && profileSidebar) {
        toggleProfile.addEventListener('click', () => {
            profileSidebar.classList.toggle("open");
            document.body.classList.toggle('shifted', profileSidebar.classList.contains('open'));
        });
    } else {
        console.error("Profile toggle button or profile sidebar not found!");
    }
});
</script>

<nav>
    <div id="logo">
        <a href="dashboard.php">
            <img src="../images/logos.png" alt="Logo" />
        </a>
    </div>
    <ul id="navbar-links">
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="myreports.php">My Reports</a></li>
        <li><a href="search_items.php">Search</a></li>
        <li><a href="submit_item.php">Submit</a></li>
        <li><a href="#" id="toggleProfile">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<aside id="profile-sidebar" class="profile-section">
    <div class="profile-content">
        <h3>Welcome, <?= htmlspecialchars($userName) ?></h3>
        
        <!-- Notification Section -->
        <div id="notifications">
            <h4>Notifications</h4>
            <ul>
                <!-- Notifications will be dynamically added here -->
            </ul>
        </div>
    </div>
    
    <!-- Profile footer with Edit and Logout at the bottom -->
    <div class="profile-footer">
        <a href="edit_profile.php">Edit Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</aside>
