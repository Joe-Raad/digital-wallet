

<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user']['id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['schedule_payment'])) {
        $wallet_id = $_POST['wallet_id'];
        $amount = (float)$_POST['amount'];
        $admin_approved = $_POST['admin_approved'];

        // Get wallet details
        $stmt = $conn->prepare("SELECT wallet_name, balance FROM wallet_profiles 
                               WHERE wallet_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $wallet_id, $user_id);
        $stmt->execute();
        $wallet = $stmt->get_result()->fetch_assoc();

        if ($wallet) {
            if ($wallet['balance'] >= $amount) {
                $stmt = $conn->prepare("INSERT INTO scheduled_payments 
                                      (user_id, wallet_id, wallet_name, amount, admin_approved)
                                      VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisds", $user_id, $wallet_id, $wallet['wallet_name'], $amount, $admin_approved);
                if ($stmt->execute()) {
                    $success = "Payment scheduled successfully!";
                } else {
                    $error = "Failed to schedule payment";
                }
            } else {
                $error = "Insufficient balance";
            }
        } else {
            $error = "Wallet not found";
        }
    }
}

// Get scheduled payments
$stmt = $conn->prepare("SELECT * FROM scheduled_payments WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Scheduled Payments</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .success { color: green; }
        .error { color: red; }
        .cancel-btn { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; }
    </style>
</head>
<body>
<header class="header">
        <div class="menu-container">
            <button class="menu-button">☰</button>
            <div class="dropdown-menu">
                <button class="dropdown-item"><a href="index.html" >Login</a></button>
                <button class="dropdown-item"><a href="index.html" >Signup</a></button>
                <button class="dropdown-item"><a href="privacy.html" >Privacy rules</a></button>
                <button class="dropdown-item"><a href="profile.php" >Profile</a></button>
                <button class="dropdown-item"><a href="security.php" >Security credentials</a></button>
                <button class="dropdown-item"><a href="create_wallet.php" >Create Wallet</a></button>
                <button class="dropdown-item"><a href="view_wallets.php" >View Wallet</a></button>
                <button class="dropdown-item"><a href="transaction_history.php" >Transaction History</a></button>
                
            </div>
        </div>
        <h1 class="title">Digital Wallet</h1>
        <a href="index.html" class="specialbutton">Logout</a>
    </header>

    <main class="main-container">
        <!-- Left Section -->
        <section class="left-section">
            <div class="faq">
                <h2 class="toggle-title">FAQs</h2>
                <p class="toggle-content"><a href="faq.html">Frequently Asked Questions go here.</a></p>
            </div>
            <div class="guides">
                <h2 class="toggle-title">Guides</h2>
                <p class="toggle-content"><a href="guides.html">Guides and instructions go here.</a></p>
            </div>
        </section>

        <!-- Middle Section -->
        <section class="middle-section">
        <div class="container">
        <h1>Scheduled Payments</h1>
        
        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <h2>Schedule New Payment</h2>
        <form method="POST">
            <div class="form-group">
                <label>Select Wallet:</label>
                <select name="wallet_id" required>
                    <?php 
                    $stmt = $conn->prepare("SELECT wallet_id, wallet_name FROM wallet_profiles WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $wallets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    foreach ($wallets as $wallet): ?>
                        <option value="<?= $wallet['wallet_id'] ?>"><?= $wallet['wallet_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Amount:</label>
                <input type="number" name="amount" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Scheduled Date/Time:</label>
                <input type="datetime-local" name="admin_approved" required>
            </div>

            <button type="submit" name="schedule_payment">Schedule Payment</button>
        </form>

        <h2>Upcoming Payments</h2>
        <table>
    <thead>
        <tr>
            <th>Wallet</th>
            <th>Amount</th>
            <th>Scheduled Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($payments as $payment): ?>
        <tr>
            <td><?= htmlspecialchars($payment['wallet_name']) ?></td>
            <td>$<?= number_format($payment['amount'], 2) ?></td>
            <td><?= date('M j, Y H:i', strtotime($payment['admin_approved'])) ?></td>
            <!-- Change status display -->
            <td><?= htmlspecialchars(ucfirst($payment['admin_approved'])) ?></td> <!-- Use htmlspecialchars for security -->
            <td>
            <?php if ($payment['admin_approved'] === 'pending'): ?> <!-- Fix status check -->
                <form action="cancel_payment.php" method="POST" style="display: inline;">
                    <input type="hidden" name="payment_id" value="<?= $payment['payment_id'] ?>">
                    <button type="submit" class="cancel-btn">Cancel</button>
                </form>
            <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </div>

        </section>

        <!-- Right Section -->
        <section class="right-section">
            <div class="news">
                <h2 class="toggle-title">News & Updates</h2>
                <p class="toggle-content"><a href="news.html" >digital wallet news appears here.</a></p>
            </div>
            <div class="privacy">
                <h2 class="toggle-title">Privacy & Regulations</h2>
                <p class="toggle-content"><a href="privacy.html">Privacy and Regulations go here.</a></p>
            </div>
            
        </section>
    </main>


    <!-- Bottom Row Sections -->
    <div class="bottom-section">
        <div class="bottom-box">A digital wallet is a financial instrument that enables electronic transactions and stores
             a user's financial information. These wallets offer easy accessibility through any connected device.
             It helps users make seamless transactions by eliminating the need for carrying physical cash or cards.</div>
        
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-info">
            <div>Phone: +961 71 327 954</div>
            <div>joeraa2002@gmail.com</div>
            <div>Address: Beirut, Al Hadath</div>
            <div>Privacy & Terms</div>
        </div>
        <p class="footer-rights">© 2025 Digital Wallet. All Rights Reserved.</p>
    </footer>

    <!-- Popup Chat Window -->
    <div class="chat-popup">
        <button class="chat-btn">Chat</button>
        <div class="chat-window">
            <!--   -->
            <textarea placeholder="Type your inquiry..."></textarea>
            <button class="send-btn">Send</button>
            <button class="close-btn">Close</button>
        </div>
    </div>

    <script src="script.js"></script>


</body>
</html>

