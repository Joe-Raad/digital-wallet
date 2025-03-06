

<?php
session_start();
require 'db.php';

// Verify admin privileges (add your admin check logic)
if (!isset($_SESSION['admin'])) {
  header('Location: admin_login.php');
  exit;
}

// Get pending payments
$stmt = $conn->prepare("SELECT * FROM scheduled_payments WHERE status = 'pending'");
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Process payment approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_payment'])) {
  $payment_id = $_POST['payment_id'];
  
  $conn->begin_transaction();
  try {
    // Get payment details
    $stmt = $conn->prepare("SELECT * FROM scheduled_payments WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();

    // Update wallet balance
    $stmt = $conn->prepare("UPDATE wallet_profiles 
                          SET balance = balance - ? 
                          WHERE wallet_id = ?");
    $stmt->bind_param("di", $payment['amount'], $payment['wallet_id']);
    $stmt->execute();

    // Record transaction
    $stmt = $conn->prepare("INSERT INTO transaction_history 
                          (wallet_id, wallet_name, amount, type, status) 
                          VALUES (?, ?, ?, 'scheduled', 'completed')");
    $stmt->bind_param("isd", 
      $payment['wallet_id'],
      $payment['wallet_name'],
      $payment['amount']
    );
    $stmt->execute();

    // Update payment status
    $stmt = $conn->prepare("UPDATE scheduled_payments 
                          SET status = 'completed', admin_processed = TRUE 
                          WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();

    $conn->commit();
    $success = "Payment processed successfully";
  } catch (Exception $e) {
    $conn->rollback();
    $error = "Processing failed: " . $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Payment Processing</title>
  <style>
    .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #ddd; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Pending Payments</h1>
    
    <?php if(isset($success)): ?>
      <div style="color:green"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
      <div style="color:red"><?= $error ?></div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>User ID</th>
          <th>Wallet</th>
          <th>Amount</th>
          <th>Scheduled Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($payments as $payment): ?>
        <tr>
          <td><?= $payment['user_id'] ?></td>
          <td><?= $payment['wallet_name'] ?></td>
          <td>$<?= number_format($payment['amount'], 2) ?></td>
          <td><?= date('M j, Y H:i', strtotime($payment['scheduled_date'])) ?></td>
          <td>
            <form method="POST">
              <input type="hidden" name="payment_id" value="<?= $payment['payment_id'] ?>">
              <button type="submit" name="approve_payment">Approve Payment</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>