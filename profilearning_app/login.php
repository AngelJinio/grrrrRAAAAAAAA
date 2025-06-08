<?php


session_start(); 

$login_message = ""; 


if (isset($_SESSION['message'])) {
    $login_message = "<p class='success-message'>" . htmlspecialchars($_SESSION['message']) . "</p>";
    unset($_SESSION['message']); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    require_once 'db_connect.php';

    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $user_type = $conn->real_escape_string($_POST['user_type']);


    $stmt = $conn->prepare("SELECT id, password, user_type FROM users WHERE username = ? AND user_type = ?");
    $stmt->bind_param("ss", $username, $user_type);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password, $db_user_type);
    $stmt->fetch();

    if ($stmt->num_rows == 1 && password_verify($password, $hashed_password)) {
    
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['user_type'] = $db_user_type;

        if ($db_user_type == 'student') {
            header("Location: student_dashboard.php");
        } elseif ($db_user_type == 'educator') {
            header("Location: educator_dashboard.php");
        }
        exit();
    } else {

        $login_message = "<p class='error-message'>Invalid username, password, or user type.</p>";
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
    <title>StudyPad - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>

        .error-message {
            color: #d9534f; 
            background-color: #fcecec;
            border: 1px solid #d9534f;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .success-message {
            color: #5cb85c; 
            background-color: #eaf7ea;
            border: 1px solid #5cb85c;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>StudyPad</h1>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="library.php">Library</a></li>
                    <li><a href="login.php" class="btn btn-primary">Login</a></li>
                    <li><a href="register.php" class="btn btn-secondary">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container content-section form-page">
        <section id="login-form-section">
            <h2>Login to Your Account</h2>
            <?php echo $login_message;  ?>
            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="user_type">Login As:</label>
                    <select id="user_type" name="user_type" required>
                        <option value="">Select Type</option>
                        <option value="student" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="educator" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'educator') ? 'selected' : ''; ?>>Educator</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-cta">Login</button>
            </form>
            <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 StudyPad. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>