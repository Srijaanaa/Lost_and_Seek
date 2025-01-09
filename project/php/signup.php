<?php
session_start();
include 'connect.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get and sanitize user inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']);

    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($phone_number)) {
        $error = "Please fill out all fields.";
    } else {
        // Validate phone number: Ensure country code + and 10-digit number
        if (!preg_match("/^\+(\d{1,4})\s?\d{10}$/", $phone_number)) {
            $error = "Invalid phone number format. Please include the country code and a 10-digit number.";
        } else {
            // Check if the username or email already exists
            $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Username or email already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_sql = "INSERT INTO users (username, email, password, full_name, phone_number, role) VALUES (?, ?, ?, ?, ?, 'user')";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $phone_number);

                if ($stmt->execute()) {
                    echo "<script>alert('Account created successfully'); window.location.href = 'login.php';</script>";
                    exit();
                } else {
                    $error = "Something went wrong. Please try again.";
                    echo "<script>alert('$error'); window.location.href = 'signup.php';</script>";
                    exit();
                }
            }

            $stmt->close();
        }
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
    <title>Signup-Lost and Seek</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<main id="container">
    <h2>Sign Up</h2>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="signup.php" method="POST" id="myform">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required placeholder="srijana"><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required placeholder="srijana7lohani@gmail.com"><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required placeholder="*******"><br>

        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" required placeholder="srijana lohani"><br>

        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number" pattern="^\+(\d{1,4})\s?\d{10}$" required placeholder="+123 1234567890"><br>

        <input type="submit" value="Sign Up" id="submit">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</main>

<footer>
    <p>&copy; <?= date("Y") ?> Lost and Seek</p>
</footer>

</body>
</html>
