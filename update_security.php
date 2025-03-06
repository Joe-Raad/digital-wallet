
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

include 'db.php';

$user_id = $_SESSION['user']['id'];
$new_email = $_POST['email'];
$new_password = $_POST['password'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if email exists (if changed)
    if ($new_email !== $_SESSION['user']['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $new_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Email already exists");
        }
    }

    // Build update query
    $updates = [];
    $params = [];
    $types = '';

    if ($new_email !== $_SESSION['user']['email']) {
        $updates[] = "email = ?";
        $params[] = $new_email;
        $types .= 's';
    }

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updates[] = "password = ?";
        $params[] = $hashed_password;
        $types .= 's';
    }

    if (!empty($updates)) {
        $types .= 'i'; // For user_id parameter
        $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        // Add user_id to params
        $params[] = $user_id;
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        // Update session email if changed
        if (isset($new_email)) {
            $_SESSION['user']['email'] = $new_email;
        }
    }

    $conn->commit();
    header('Location: security.php?success=1');
} catch (Exception $e) {
    $conn->rollback();
    header('Location: security.php?error=' . urlencode($e->getMessage()));
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>