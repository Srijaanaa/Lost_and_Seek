<?php
session_start();
include 'connect.php';

// Security Headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'");

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = "";

// Rate Limit (5 attempts per 10 min)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}
if (time() - $_SESSION['last_attempt_time'] > 600) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            $_SESSION['login_attempts'] = 0;

            if ($row['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $error_message = "Invalid username or password.";
        }
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $error_message = "Invalid username or password.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login - Lost and Seek</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .password-container input {
            width: 100%;
            padding-right: 40px; /* Space for the toggle button */
        }

        .password-container .toggle-password {
            position: absolute;
            right: 10px; /* Position on the right side */
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }
    </style>
</head>
<body>

    <main id="container">
        <h2>Login Page</h2>

        <form id="myform" action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <button type="button" class="toggle-password" id="togglePassword">👁️</button>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember Me</label><br>
            </div>

            <input type="submit" id="submit" value="Login">
            <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        </form>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Lost and Seek</p>
    </footer>

    <script>
        // Toggle password visibility
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
