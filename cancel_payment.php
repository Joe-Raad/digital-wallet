
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user']['id'];
$payment_id = $_POST['payment_id'];

// Delete payment if status is pending
$stmt = $conn->prepare("DELETE FROM scheduled_payments 
                       WHERE payment_id = ? 
                       AND user_id = ? 
                       AND addmin_approved = 'pending'");
$stmt->bind_param("ii", $payment_id, $user_id);
$stmt->execute();

header("Location: scheduled_payments.php");
exit;
?>