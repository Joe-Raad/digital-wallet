
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

include 'db.php';

$user_id = $_SESSION['user']['id'];
$username = $_POST['username'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$facebook = $_POST['facebook'];

// Handle file upload
$id_document = '';
if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_name = uniqid() . '_' . basename($_FILES['id_document']['name']);
    $target_file = $upload_dir . $file_name;
    
    // Validate file type
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    if (in_array($file_type, $allowed_types)) {
        move_uploaded_file($_FILES['id_document']['tmp_name'], $target_file);
        $id_document = $target_file;
    }
}

// Update or insert profile data
$stmt = $conn->prepare("INSERT INTO user_profiles 
    (user_id, username, id_document, address, phone, facebook) 
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    id_document = COALESCE(VALUES(id_document), id_document),
    address = VALUES(address),
    phone = VALUES(phone),
    facebook = VALUES(facebook)");

$stmt->bind_param("isssss", $user_id, $username, $id_document, $address, $phone, $facebook);

if ($stmt->execute()) {
    header('Location: profile.php');
} else {
    die("Error saving profile: " . $conn->error);
}

$stmt->close();
$conn->close();
?>