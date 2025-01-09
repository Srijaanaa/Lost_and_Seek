<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login-Lost and Seek</title>
    <link rel="stylesheet" href="../css/style.css"> 
</head>
<body>

<?php
session_start();
include 'connect.php';

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            session_regenerate_id(); 

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; 

            if ($row['role'] == 'admin') {
                header("Location: admin.php");  
            } else {
                header("Location: dashboard.php");  
            }
            exit();
        } else {
            $error_message = "Invalid username or password.";
        }
    } else {
        $error_message = "Invalid username or password.";
    }
    $conn->close();
}
?>

    <main id="container">
        <h2>  Login page  </h2>

        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form id="myform" action="" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

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

</body>
</html>
