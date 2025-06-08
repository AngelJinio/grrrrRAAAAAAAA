<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    require_once 'db_connect.php';

    $material_id = $conn->real_escape_string($_GET['id']);

    $stmt = $conn->prepare("SELECT title, file_path FROM materials WHERE id = ?");
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($material_title, $file_path);
    $stmt->fetch();

    if ($stmt->num_rows == 1) {
        $full_file_path = __DIR__ . '/' . $file_path;

        if (file_exists($full_file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream'); 
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($full_file_path));
            readfile($full_file_path);
            exit;
        } else {
            echo "Error: File not found on server.";
        }
    } else {
        echo "Error: Material not found in database.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Error: Material ID not provided.";
}
?>