<?php
session_start();
include 'connect.php';

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Trim and sanitize inputs
    $username = trim($_POST['username']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $full_name = htmlspecialchars(trim($_POST['full_name']), ENT_QUOTES, 'UTF-8');
    $phone_number = trim($_POST['phone_number']);

    // Validate required fields
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($phone_number)) {
        die("All fields are required.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Validate username (only letters, numbers, and underscores)
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        die("Username must contain only letters, numbers, and underscores.");
    }

    // Validate phone number (Country code + 10-digit number)
    if (!preg_match("/^\+(\d{1,4})\s?\d{10}$/", $phone_number)) {
        die("Invalid phone number format.");
    }

    // Password security: At least 8 chars, one upper, one lower, one number, one special character
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        die("Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a number, and a special character.");
    }

    // Check if username or email already exists
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Username or email is already taken.");
    }

    $stmt->close();

    // Hash the password securely
    $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

    // Insert into database
    $insert_sql = "INSERT INTO users (username, email, password, full_name, phone_number, role) VALUES (?, ?, ?, ?, ?, 'user')";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $phone_number);

    if ($stmt->execute()) {
        echo "<script>alert('Account created successfully'); window.location.href = 'login.php';</script>";
        exit();
    } else {
        die("Something went wrong. Please try again.");
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Signup - Lost and Seek</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    /* Container for password input and toggle button */
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        /* Style for password input */
        #password {
            width: 100%;
            padding-right: 40px; /* Space for the eye button */
        }

        /* Style for eye button */
        #togglePassword {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }
    </style>

</head>
<body>

<main id="container">
    <h2>Sign Up</h2>
    <form action="signup.php" method="POST" id="myform">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required pattern="^[a-zA-Z0-9_]+$" title="Only letters, numbers, and underscores allowed." placeholder="eg. srijana"><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required placeholder="eg. srijana7lohani@gmail.com"><br>

        <label for="password">Password:</label>
        <div class="password-container">
            <input type="password" id="password" name="password" required 
                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                title="At least 8 characters, one uppercase, one lowercase, one number, and one special character."
                placeholder="*******">
            <button type="button" id="togglePassword">👁️</button>
        </div>

        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" required placeholder="eg. srijana lohani"><br>

        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number" required pattern="^\+977(97|98)\d{8}$"
        title="Should start with +97797 or +97798 followed by 8 digits."
       placeholder="eg. +97798********" ><br>


        <input type="submit" value="Sign Up" id="submit">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</main>

<footer>
    <p>&copy; <?= date("Y") ?> Lost and Seek</p>
</footer>
<script>
    document.getElementById("togglePassword").addEventListener("click", function() {
            var passwordField = document.getElementById("password");
            var toggleButton = document.getElementById("togglePassword");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleButton.textContent = "🙈"; // Change icon to "monkey"
            } else {
                passwordField.type = "password";
                toggleButton.textContent = "👁️"; // Change icon to "eye"
            }
        });

        // Check if error message is set and display it as an alert
        <?php if (!empty($error_message)): ?>
            alert("<?php echo addslashes($error_message); ?>");
        <?php endif; ?>
</script>
</body>
</html>
