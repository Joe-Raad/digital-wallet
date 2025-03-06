

<?php  
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

// Add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user']['id'];
$wallet_name = $_POST['wallet_name'] ?? '';
$wallet_type = $_POST['wallet_type'] ?? '';

// Debug: Check received data
error_log("User ID: $user_id, Wallet Name: $wallet_name, Type: $wallet_type");

// Validate input
if (empty($wallet_name) || empty($wallet_type)) {
    die("Missing required fields");
}

try {
    // Check for existing wallet name
    $stmt = $conn->prepare("SELECT wallet_id FROM wallet_profiles 
                          WHERE user_id = ? AND wallet_name = ?");
    $stmt->bind_param("is", $user_id, $wallet_name);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        header('Location: create_wallet.php?error=Wallet+name+exists');
        exit;
    }

    // Insert new wallet
    $stmt = $conn->prepare("INSERT INTO wallet_profiles 
                          (user_id, wallet_name, wallet_type, balance) 
                          VALUES (?, ?, ?, 0.00)");
    $stmt->bind_param("iss", $user_id, $wallet_name, $wallet_type);
    
    if ($stmt->execute()) {
        error_log("Wallet created successfully. ID: " . $stmt->insert_id);
        header('Location: view_wallets.php');
    } else {
        error_log("Insert failed: " . $stmt->error);
        header('Location: create_wallet.php?error=Creation+failed');
    }
    
} catch (mysqli_sql_exception $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: create_wallet.php?error=Database+error');
} 
?>