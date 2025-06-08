<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'educator') {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['material_id'])) {
    require_once 'db_connect.php';

    $material_id = $conn->real_escape_string($_POST['material_id']);
    $educator_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT file_path FROM materials WHERE id = ? AND educator_id = ?");
    $stmt->bind_param("ii", $material_id, $educator_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($file_path);
    $stmt->fetch();

    if ($stmt->num_rows == 1) {
        $full_file_path = __DIR__ . '/' . $file_path;
        $stmt->close(); 
 
        $stmt_delete = $conn->prepare("DELETE FROM materials WHERE id = ? AND educator_id = ?");
        $stmt_delete->bind_param("ii", $material_id, $educator_id);

        if ($stmt_delete->execute()) {

            if (file_exists($full_file_path)) {
                unlink($full_file_path);
                $_SESSION['upload_message'] = "Material and file deleted successfully!";
            } else {
                $_SESSION['upload_message'] = "Material deleted from database, but file not found on server.";
            }
        } else {
            $_SESSION['error_message'] = "Error deleting material from database: " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $_SESSION['error_message'] = "Material not found or you don't have permission to delete it.";
    }
    $conn->close();
    header("Location: educator_dashboard.php");
    exit();
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: educator_dashboard.php");
    exit();
}
?>