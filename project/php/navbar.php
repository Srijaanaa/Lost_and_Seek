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
    background-color: #000080;
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
    transition: color 0.3s;
}

nav ul li a:hover {
    color: #ffcc00;
}

#profile-sidebar {
    position: fixed;
    top: 0;
    right: -20%;
    width: 20%;
    height: 100%;
    background-color: #000080;
    color: white;
    padding: 20px;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
    transition: right 0.3s ease, width 0.3s ease;
}

#profile-sidebar.open {
    right: 0;
}
.profile-content h3{
    margin: 50px;
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
}
.profile-content h3{
    color:white;
}

</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const profileSidebar = document.getElementById("profile-sidebar");
    const toggleProfile = document.getElementById("toggleProfile");

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
            <img src="../images/logo.png" alt="Logo" />
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
        <a href="edit_profile.php">Edit Profile</a><br>
        <a href="logout.php">Logout</a>
    </div>
</aside>
