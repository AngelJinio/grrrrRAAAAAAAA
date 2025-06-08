<?php

session_start(); 

$registration_message = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once 'db_connect.php';

    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; 
    $confirm_password = $_POST['confirm_password'];
    $user_type = $conn->real_escape_string($_POST['user_type']);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($user_type)) {
        $registration_message = "<p class='error-message'>All fields are required.</p>";
    } elseif ($password !== $confirm_password) {
        $registration_message = "<p class='error-message'>Passwords do not match.</p>";
    } elseif (strlen($password) < 6) {
        $registration_message = "<p class='error-message'>Password must be at least 6 characters long.</p>";
    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $registration_message = "<p class='error-message'>Username or Email already exists.</p>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, user_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed_password, $email, $user_type);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $registration_message = "<p class='error-message'>Error during registration: " . $stmt->error . "</p>";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyPad - Register</title>
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
        <section id="register-form-section">
            <h2>Create an Account</h2>
            <?php echo $registration_message; ?>
            <form action="register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="reg_username">Username:</label>
                    <input type="text" id="reg_username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="reg_email">Email:</label>
                    <input type="email" id="reg_email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="reg_password">Password:</label>
                    <input type="password" id="reg_password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="reg_confirm_password">Confirm Password:</label>
                    <input type="password" id="reg_confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label for="reg_user_type">Register As:</label>
                    <select id="reg_user_type" name="user_type" required>
                        <option value="">Select Type</option>
                        <option value="student" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="educator" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'educator') ? 'selected' : ''; ?>>Educator</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-cta">Register</button>
            </form>
            <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 StudyPad. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>