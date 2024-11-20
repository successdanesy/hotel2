<?php 
session_start();
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

            <form id="login-form" method="POST" action="server.php">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Enter your username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="**************" required>
                </div>
                <button type="submit" class="login-btn" name="login_user">Log In</button>
            </form>
        </div>
    </div>
</body>
</html>
