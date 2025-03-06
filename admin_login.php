
<?php

include 'db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);

// Common validations
if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$email = $data['email'];
$password = $data['password'];
$action = $data['action'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Handle different actions
if ($action === 'signup') {
    handleAdminSignup($conn, $email, $password);
} elseif ($action === 'login') {
    handleAdminLogin($conn, $email, $password);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function handleAdminSignup($conn, $email, $password) {
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new admin
    $stmt = $conn->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION['admin'] = [
            'id' => $stmt->insert_id,
            'email' => $email
        ];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $conn->error]);
    }

    $stmt->close();
}

function handleAdminLogin($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = [
                'id' => $admin['id'],
                'email' => $email
            ];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Admin not found']);
    }

    $stmt->close();
}

$conn->close();


?>
