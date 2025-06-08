<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php"); 
    exit();
}

require_once 'db_connect.php';

$student_username = $_SESSION['username'];
$student_id = $_SESSION['user_id'];

$subjects = [];
$stmt = $conn->prepare("
    SELECT DISTINCT s.id, s.name, s.description
    FROM subjects s
    JOIN materials m ON s.id = m.subject_id
    ORDER BY s.name
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();

$materials_by_subject = [];
foreach ($subjects as $subject) {
    $stmt = $conn->prepare("SELECT id, title, file_path FROM materials WHERE subject_id = ? ORDER BY title");
    $stmt->bind_param("i", $subject['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $materials_by_subject[$subject['id']][] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - StudyPad</title>
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
                    <li><a href="student_dashboard.php" class="btn btn-primary">Student Dashboard</a></li>
                    <li><a href="logout.php" class="btn btn-secondary">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container content-section">
        <section id="student-dashboard">
            <h2>Welcome, <?php echo htmlspecialchars($student_username); ?>!</h2>
            <p>Here are your available subjects and learning materials.</p>

            <div class="dashboard-section">
                <h3>My Subjects</h3>
                <?php if (empty($subjects)): ?>
                    <p>No subjects with available materials yet. Check back later!</p>
                <?php else: ?>
                    <div class="subject-grid">
                        <?php foreach ($subjects as $subject): ?>
                            <div class="subject-card">
                                <h4><?php echo htmlspecialchars($subject['name']); ?></h4>
                                <p><?php echo htmlspecialchars($subject['description']); ?></p>
                                <a href="#subject-<?php echo $subject['id']; ?>" class="btn-small">View Materials</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php foreach ($subjects as $subject): ?>
            <div class="dashboard-section" id="subject-<?php echo $subject['id']; ?>">
                <h3><?php echo htmlspecialchars($subject['name']); ?> Materials</h3>
                <?php if (!empty($materials_by_subject[$subject['id']])): ?>
                    <div class="material-list">
                        <?php foreach ($materials_by_subject[$subject['id']] as $material): ?>
                            <div class="material-item">
                                <span><?php echo htmlspecialchars($material['title']); ?></span>
                                <a href="download.php?id=<?php echo $material['id']; ?>" class="btn-download">Download</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No materials available for <?php echo htmlspecialchars($subject['name']); ?> yet.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 StudyPad. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>