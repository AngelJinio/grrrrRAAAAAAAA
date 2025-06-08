<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'educator') {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['material_file'])) {
    require_once 'db_connect.php';

    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $educator_id = $_SESSION['user_id'];

    $target_dir = "uploads/"; 
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $original_file_name = basename($_FILES["material_file"]["name"]);
    $file_extension = pathinfo($original_file_name, PATHINFO_EXTENSION);
    $unique_file_name = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $unique_file_name;
    $uploadOk = 1;
    $file_type = strtolower($file_extension);

    if (file_exists($target_file)) {
        $_SESSION['error_message'] = "Sorry, file already exists.";
        $uploadOk = 0;
    }

    if ($_FILES["material_file"]["size"] > 50000000) {
        $_SESSION['error_message'] = "Sorry, your file is too large (max 50MB).";
        $uploadOk = 0;
    }

    $allowed_types = array("pdf", "doc", "docx", "ppt", "pptx", "xls", "xlsx", "txt", "jpg", "jpeg", "png", "gif");
    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['error_message'] = "Sorry, only PDF, DOCX, PPTX, XLS, TXT, JPG, JPEG, PNG, GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $_SESSION['error_message'] .= " Your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["material_file"]["tmp_name"], $target_file)) {
            
            $stmt = $conn->prepare("INSERT INTO materials (subject_id, educator_id, title, file_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $subject_id, $educator_id, $title, $target_file);

            if ($stmt->execute()) {
                $_SESSION['upload_message'] = "The file ". htmlspecialchars($original_file_name). " has been uploaded and details saved.";
            } else {
                $_SESSION['error_message'] = "Error saving file details to database: " . $stmt->error;
                unlink($target_file); 
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
        }
    }
    $conn->close();
    header("Location: educator_dashboard.php"); 
} else {
    $_SESSION['error_message'] = "Invalid request or no file uploaded.";
    header("Location: educator_dashboard.php");
    exit();
}
?>