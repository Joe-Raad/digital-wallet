

<?php
session_start();
require 'db.php';

// Verify admin privileges (add your admin check logic here)
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

// Get all pending payments
$stmt = $conn->prepare("SELECT * FROM scheduled_payments WHERE admin_approved = 'pending'");
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle admin approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_payment'])) {
    $payment_id = $_POST['payment_id'];
    
    $stmt = $conn->prepare("UPDATE scheduled_payments 
                          SET admin_approved = 'completed' 
                          WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    
    header("Location: admin_payments.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Payment Approvals</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        .approve-btn { background: #28a745; color: white; padding: 5px 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pending Payments Approval</h1>
        <table>
            <tr>
                <th>User ID</th>
                <th>Wallet</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
            <?php foreach ($payments as $payment): ?>
            <tr>
                <td><?= $payment['user_id'] ?></td>
                <td><?= $payment['wallet_name'] ?></td>
                <td>$<?= number_format($payment['amount'], 2) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="payment_id" value="<?= $payment['payment_id'] ?>">
                        <button type="submit" name="approve_payment" class="approve-btn">Approve</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>