<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'educator') {
    header("Location: login.php"); 
    exit();
}

require_once 'db_connect.php';

$educator_username = $_SESSION['username'];
$educator_id = $_SESSION['user_id'];

$upload_message = "";
if (isset($_SESSION['upload_message'])) {
    $upload_message = "<p class='success-message'>" . htmlspecialchars($_SESSION['upload_message']) . "</p>";
    unset($_SESSION['upload_message']);
}
if (isset($_SESSION['error_message'])) {
    $upload_message = "<p class='error-message'>" . htmlspecialchars($_SESSION['error_message']) . "</p>";
    unset($_SESSION['error_message']);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $new_subject_name = $conn->real_escape_string($_POST['new_subject_name']);
    $new_subject_description = $conn->real_escape_string($_POST['new_subject_description']);

    if (!empty($new_subject_name)) {
        $stmt = $conn->prepare("INSERT INTO subjects (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_subject_name, $new_subject_description);
        if ($stmt->execute()) {
            $_SESSION['upload_message'] = "Subject '{$new_subject_name}' added successfully!";
        } else {
            $_SESSION['error_message'] = "Error adding subject: " . $stmt->error;
        }
        $stmt->close();
        header("Location: educator_dashboard.php"); 
        exit();
    } else {
        $_SESSION['error_message'] = "Subject name cannot be empty.";
        header("Location: educator_dashboard.php");
        exit();
    }
}


$subjects = [];
$stmt = $conn->prepare("SELECT id, name, description FROM subjects ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();

$materials_by_subject = [];
foreach ($subjects as $subject) {
    $stmt = $conn->prepare("SELECT id, title, file_path FROM materials WHERE subject_id = ? AND educator_id = ? ORDER BY title");
    $stmt->bind_param("ii", $subject['id'], $educator_id);
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
    <title>Educator Dashboard - StudyPad</title>
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
                    <li><a href="educator_dashboard.php" class="btn btn-primary">Educator Dashboard</a></li>
                    <li><a href="logout.php" class="btn btn-secondary">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container content-section">
        <section id="educator-dashboard">
            <h2>Welcome, <?php echo htmlspecialchars($educator_username); ?>!</h2>
            <p>Manage your subjects and upload new learning materials.</p>

            <?php echo $upload_message; ?>

            <div class="dashboard-section">
                <h3>Your Subjects</h3>
                <div class="subject-grid">
                    <?php foreach ($subjects as $subject): ?>
                        <div class="subject-card">
                            <h4><?php echo htmlspecialchars($subject['name']); ?></h4>
                            <p><?php echo htmlspecialchars($subject['description']); ?></p>
                            <a href="#manage-subject-<?php echo $subject['id']; ?>" class="btn-small">Manage Materials</a>
                        </div>
                    <?php endforeach; ?>
                    <div class="subject-card add-new">
                        <h4>Add New Subject</h4>
                        <p>Create a new subject for your materials.</p>
                        <button class="btn-small" onclick="document.getElementById('add-subject-form-section').style.display = 'block';">Add Subject</button>
                    </div>
                </div>
            </div>

            <div class="dashboard-section" id="add-subject-form-section" style="display:none;">
                <h3>Add New Subject</h3>
                <form action="educator_dashboard.php" method="POST" class="upload-form">
                    <div class="form-group">
                        <label for="new_subject_name">Subject Name:</label>
                        <input type="text" id="new_subject_name" name="new_subject_name" required>
                    </div>
                    <div class="form-group">
                        <label for="new_subject_description">Description (optional):</label>
                        <textarea id="new_subject_description" name="new_subject_description" rows="3"></textarea>
                    </div>
                    <button type="submit" name="add_subject" class="btn btn-cta">Add Subject</button>
                </form>
            </div>


            <?php foreach ($subjects as $subject): ?>
            <div class="dashboard-section" id="manage-subject-<?php echo $subject['id']; ?>">
                <h3>Manage <?php echo htmlspecialchars($subject['name']); ?> Materials</h3>
                <div class="material-management-section">
                    <h4>Upload New Material</h4>
                    <form action="upload_material.php" method="POST" enctype="multipart/form-data" class="upload-form">
                        <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                        <div class="form-group">
                            <label for="material_title_<?php echo $subject['id']; ?>">Material Title:</label>
                            <input type="text" id="material_title_<?php echo $subject['id']; ?>" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="material_file_<?php echo $subject['id']; ?>">Select File:</label>
                            <input type="file" id="material_file_<?php echo $subject['id']; ?>" name="material_file" required>
                        </div>
                        <button type="submit" class="btn btn-cta">Upload Material</button>
                    </form>
                </div>

                <h4>Existing Materials</h4>
                <?php if (!empty($materials_by_subject[$subject['id']])): ?>
                    <div class="material-list">
                        <?php foreach ($materials_by_subject[$subject['id']] as $material): ?>
                            <div class="material-item">
                                <span><?php echo htmlspecialchars($material['title']); ?></span>
                                <a href="download.php?id=<?php echo $material['id']; ?>" class="btn-download">View/Download</a>
                                <form action="delete_material.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete this material?');">Delete</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No materials uploaded for this subject yet.</p>
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