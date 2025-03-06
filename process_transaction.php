


<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

// Ensure POST variables are set
$wallet_name = $_POST['wallet_name'] ?? null;
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.00;
$type = $_POST['type'] ?? null;

if (!$wallet_name || !$type) {
    die("Missing required transaction details");
}

if ($amount <= 0) {
    die("Invalid transaction amount");
}

$user_id = $_SESSION['user']['id'];

// Verify wallet ownership and get wallet_id
$stmt = $conn->prepare("SELECT wallet_id FROM wallet_profiles WHERE wallet_name = ? AND user_id = ?");
$stmt->bind_param("si", $wallet_name, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$wallet = $result->fetch_assoc();

if (!$wallet) {
    die("Invalid wallet access");
}

$wallet_id = $wallet['wallet_id'];

// Get user tier
$tier_stmt = $conn->prepare("SELECT tier FROM user_profiles WHERE user_id = ?");
$tier_stmt->bind_param("i", $user_id);
$tier_stmt->execute();
$result = $tier_stmt->get_result();
$tier_data = $result->fetch_assoc();
$tier = $tier_data['tier'] ?? 'unverified';

// Set limits based on tier
$limits = [
    'verified' => [
        'daily' => 2000,
        'weekly' => 14000,
        'monthly' => 60000
    ],
    'unverified' => [
        'daily' => 500,
        'weekly' => 3500,
        'monthly' => 15000
    ]
];

// Check existing transactions
$checks = [
    'daily' => 'AND transaction_date >= CURDATE()',
    'weekly' => 'AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
    'monthly' => 'AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)'
];

foreach ($checks as $period => $condition) {
    $stmt = $conn->prepare("SELECT SUM(amount) AS total 
                          FROM transaction_history 
                          WHERE wallet_id = ? 
                          AND type IN ('deposit', 'withdraw')
                          $condition");
    $stmt->bind_param("i", $wallet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_data = $result->fetch_assoc();
    $total = $total_data['total'] ?? 0;

    if (($total + $amount) > $limits[$tier][$period]) {
        header("Location: wallet_management.php?wallet_name=" . urlencode($wallet_name) . "&error=Exceeded $period limit");
        exit;
    }
}

// Record transaction
try {
    $stmt = $conn->prepare("INSERT INTO transaction_history 
                          (wallet_id, amount, type) 
                          VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $wallet_id, $amount, $type);
    $stmt->execute();

    header("Location: wallet_management.php?wallet_name=" . urlencode($wallet_name));
} catch (Exception $e) {
    header("Location: wallet_management.php?wallet_name=" . urlencode($wallet_name) . "&error=Transaction failed");
}
?>
