

<?php
session_start();
require 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user']['id'];
$sender_wallet_id = $_POST['sender_wallet_id'];
$recipient_wallet_name = trim($_POST['recipient_wallet_name']);
$amount = (float)$_POST['amount'];

try {
    // Get sender wallet details
    $stmt = $conn->prepare("SELECT wallet_id, wallet_name, balance 
                          FROM wallet_profiles 
                          WHERE wallet_id = ? AND user_id = ?");
    if(!$stmt) throw new Exception("Prepare failed: ".$conn->error);
    $stmt->bind_param("ii", $sender_wallet_id, $user_id);
    $stmt->execute();
    $sender_wallet = $stmt->get_result()->fetch_assoc();

    if (!$sender_wallet) throw new Exception("Sender wallet not found");
    
    // Get recipient wallet details
    $stmt = $conn->prepare("SELECT wallet_id, wallet_name 
                          FROM wallet_profiles 
                          WHERE wallet_name = ?");
    if(!$stmt) throw new Exception("Prepare failed: ".$conn->error);
    $stmt->bind_param("s", $recipient_wallet_name);
    $stmt->execute();
    $recipient_wallet = $stmt->get_result()->fetch_assoc();

    if (!$recipient_wallet) throw new Exception("Recipient wallet not found");
    
    // Check balance
    if ($sender_wallet['balance'] < $amount) {
        throw new Exception("Insufficient funds");
    }

    $conn->begin_transaction();

    // Update balances
    $stmt = $conn->prepare("UPDATE wallet_profiles 
                          SET balance = balance - ? 
                          WHERE wallet_id = ?");
    $stmt->bind_param("di", $amount, $sender_wallet_id);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE wallet_profiles 
                          SET balance = balance + ? 
                          WHERE wallet_id = ?");
    $stmt->bind_param("di", $amount, $recipient_wallet['wallet_id']);
    $stmt->execute();

    // Record transactions with full details
    $stmt = $conn->prepare("INSERT INTO transaction_history 
                          (wallet_id, wallet_name, amount, type, 
                          status, sender_wallet_name, recipient_wallet_name) 
                          VALUES (?, ?, ?, 'transfer', 'completed', ?, ?)");
    
    // Sender's transaction record
    $negative_amount = -$amount;
    $stmt->bind_param("issss", 
        $sender_wallet_id,
        $sender_wallet['wallet_name'],
        $negative_amount,
        $sender_wallet['wallet_name'],
        $recipient_wallet['wallet_name']
    );
    $stmt->execute();

    // Recipient's transaction record
    $stmt->bind_param("issss", 
        $recipient_wallet['wallet_id'],
        $recipient_wallet['wallet_name'],
        $amount,
        $sender_wallet['wallet_name'],
        $recipient_wallet['wallet_name']
    );
    $stmt->execute();

    $conn->commit();
    
    header("Location: wallet_management.php?wallet_id=$sender_wallet_id&success=Transfer+completed");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log("Transfer Error: ".$e->getMessage());
    header("Location: wallet_management.php?wallet_id=$sender_wallet_id&error=".urlencode($e->getMessage()));
    exit;
}
?>