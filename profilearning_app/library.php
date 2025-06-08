<?php

session_start();
require_once 'db_connect.php';

$subjects = [];
$stmt = $conn->prepare("SELECT id, name, description FROM subjects ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyPad - Library</title>
    <link rel="stylesheet" href="style.css">
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo ($_SESSION['user_type'] == 'student' ? 'student_dashboard.php' : 'educator_dashboard.php'); ?>" class="btn btn-primary">Dashboard</a></li>
                        <li><a href="logout.php" class="btn btn-secondary">Logout</a></li>
                    <?php else:  ?>
                        <li><a href="login.php" class="btn btn-primary">Login</a></li>
                        <li><a href="register.php" class="btn btn-secondary">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container content-section">
        <section id="library-content">
            <h2>Our Subjects</h2>
            <p>Explore learning materials categorized by subject. Login as a student to view and download materials!</p>

            <?php if (empty($subjects)): ?>
                <p>No subjects are available yet.</p>
            <?php else: ?>
                <div class="subject-grid">
                    <?php foreach ($subjects as $subject): ?>
                        <div class="subject-card">
                            <h3><?php echo htmlspecialchars($subject['name']); ?></h3>
                            <p><?php echo htmlspecialchars($subject['description']); ?></p>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'student'): ?>
                                <a href="student_dashboard.php#subject-<?php echo $subject['id']; ?>" class="btn-small">View Materials</a>
                            <?php else: ?>
                                <a href="login.php" class="btn-small">Login to View</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 StudyPad. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>