<?php 
session_start();
include('db_connect.php'); // Include the database connection

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_user'])) {
    // Get form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to get the stored hashed password and role from the database
    $query = "SELECT username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the username exists in the database
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify the entered password against the hashed password stored in the database
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Store role for access control

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: home.php');
                exit();
            } elseif ($user['role'] === 'kitchen') {
                header('Location: kitchen.php');
                exit();
            } elseif ($user['role'] === 'bar') {
                header('Location: bar.php');
                exit();
            } elseif ($user['role'] === 'manager') {
                header('Location: manager.php');
                exit();
            } else {
                $_SESSION['msg'] = "Invalid user role.";
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['msg'] = "Invalid username or password.";
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['msg'] = "User not found.";
        header('Location: login.php');
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | Antilla Apartments & Suites</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="image-section">
            <!-- Placeholder for logo or image -->
        </div>
        <div class="form-section">
            <h1 class="logo">Antilla Apartments & Suites <br>Staff Login</h1>
            
            <!-- Display success or error messages -->
            <?php 
            if (isset($_SESSION['msg'])) : 
            ?>
            <div class="error success">
                <h3>
                    <?php 
                    echo $_SESSION['msg']; 
                    unset($_SESSION['msg']);
                    ?>
                </h3>
            </div>
            <?php endif ?>

            <form id="login-form" method="POST" action="login.php">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Enter your username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="**************" required>
                </div>
                <h3>Note: Please Make Sure Your System Date Is Correct</h3>
                <br>
                <button type="submit" class="login-btn" name="login_user">Log In</button>
            </form>
        </div>
    </div>
</body>
</html>
