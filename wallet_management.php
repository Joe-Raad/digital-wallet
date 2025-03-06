


<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Get the wallet ID from the URL
if (!isset($_GET['wallet_id'])) {
    die("No wallet selected.");
}
$wallet_id = $_GET['wallet_id'];

// Get wallet details
$stmt = $conn->prepare("SELECT balance FROM wallet_profiles WHERE wallet_id = ?");
$stmt->bind_param("i", $wallet_id);
$stmt->execute();
$wallet = $stmt->get_result()->fetch_assoc();
$balance = $wallet['balance'] ?? 0.00;


if (!$wallet) {
    die("Wallet not found.");
}

// Get the wallet ID from the URL
$wallet_id = $_GET['wallet_id'] ?? null;
if (!$wallet_id) {
    die("No wallet selected.");
}

// Get wallet details with proper error handling
$stmt = $conn->prepare("SELECT 
    wallet_id, 
    wallet_name, 
    wallet_type, 
    balance 
    FROM wallet_profiles 
    WHERE wallet_id = ? AND user_id = ?");
$stmt->bind_param("ii", $wallet_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Verify wallet exists and belongs to user
if ($result->num_rows === 0) {
    die("Wallet not found or access denied.");
}

$wallet = $result->fetch_assoc();

// Safely extract values using null coalescing operator
$wallet_name = $wallet['wallet_name'] ?? 'N/A';
$wallet_type = $wallet['wallet_type'] ?? 'Unknown';
$balance = $wallet['balance'] ?? 0.00;



// Get the user's tier
$stmt = $conn->prepare("SELECT tier FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tier_result = $stmt->get_result();
$tier_data = $tier_result->fetch_assoc();
$tier = $tier_data['tier'] ?? 'unverified';

// Tier-based transaction limits
$limits = [
    'verified' => ['daily' => 2000, 'weekly' => 14000, 'monthly' => 60000],
    'unverified' => ['daily' => 500, 'weekly' => 3500, 'monthly' => 15000]
];

// Function to record transactions in database
function recordTransaction($conn, $wallet_id, $wallet_name, $amount, $type) {
    $stmt = $conn->prepare("INSERT INTO transaction_history (wallet_id, wallet_name, amount, type, transaction_date, status) VALUES (?, ?, ?, ?, NOW(), 'completed')");
    $stmt->bind_param("isds", $wallet_id, $wallet_name, $amount, $type);
    if (!$stmt->execute()) {
        die("Transaction failed: " . $stmt->error);
    }
}

// Handle deposit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deposit'])) {
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.00;

    if ($amount <= 0) {
        $error = "Invalid deposit amount.";
    } else {
        // Check limits
        $checks = [
            'daily' => "AND transaction_date >= CURDATE()",
            'weekly' => "AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
            'monthly' => "AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"
        ];

        foreach ($checks as $period => $condition) {
            $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM transaction_history WHERE wallet_id = ? AND type = 'deposit' $condition");
            $stmt->bind_param("i", $wallet_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_data = $result->fetch_assoc();
            $total = $total_data['total'] ?? 0;

            if (($total + $amount) > $limits[$tier][$period]) {
                $error = "Exceeded $period deposit limit.";
                break;
            }
        }

        if (!isset($error)) {
            // Update balance
            $stmt = $conn->prepare("UPDATE wallet_profiles SET balance = balance + ? WHERE wallet_id = ?");
            $stmt->bind_param("di", $amount, $wallet_id);
            $stmt->execute();

            // Record transaction
            recordTransaction($conn, $wallet_id, $wallet_name, $amount, 'deposit');

            // Refresh wallet data
            header("Location: wallet_management.php?wallet_id=$wallet_id&success=deposit");
            exit;
        }
    }
}

// Handle withdrawal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.00;

    if ($amount <= 0) {
        $error = "Invalid withdrawal amount.";
    } elseif ($amount > $wallet['balance']) {
        $error = "Insufficient balance.";
    } else {
        // Check limits
        $checks = [
            'daily' => "AND transaction_date >= CURDATE()",
            'weekly' => "AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
            'monthly' => "AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"
        ];

        foreach ($checks as $period => $condition) {
            $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM transaction_history WHERE wallet_id = ? AND type = 'withdraw' $condition");
            $stmt->bind_param("i", $wallet_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_data = $result->fetch_assoc();
            $total = $total_data['total'] ?? 0;

            if (($total + $amount) > $limits[$tier][$period]) {
                $error = "Exceeded $period withdrawal limit.";
                break;
            }
        }

        if (!isset($error)) {
            // Update balance
            $stmt = $conn->prepare("UPDATE wallet_profiles SET balance = balance - ? WHERE wallet_id = ?");
            $stmt->bind_param("di", $amount, $wallet_id);
            $stmt->execute();

            // Record transaction
            recordTransaction($conn, $wallet_id, $wallet_name, $amount, 'withdraw');

            // Refresh wallet data
            header("Location: wallet_management.php?wallet_id=$wallet_id&success=withdraw");
            exit;
        }
    }
}       
?>

<?php   


$user_id = $_SESSION['user']['id'];

// Fetch user wallets
$stmt = $conn->prepare("SELECT wallet_name FROM wallet_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$wallets = $result->fetch_all(MYSQLI_ASSOC);

?>
    

<!DOCTYPE html>
<html>
<head>
    <title>Wallet Management</title>
    <link rel="stylesheet" href="styles.css">
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
        <h1>Wallet Management</h1>
        <p><strong>Wallet Name:</strong> <?= htmlspecialchars($wallet_name) ?></p>
        <p><strong>Wallet Type:</strong> <?= htmlspecialchars($wallet_type) ?></p>
        <p><strong>Balance:</strong> $<?= number_format((float)$balance, 2) ?></p>
        
        <!-- Update display -->
        <p><strong>Balance:</strong> $<?= number_format($balance, 2) ?></p>
         

        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Transaction successful!</p>
        <?php endif; ?>

        <h2>Deposit</h2>
        <form method="POST">
            <input type="number" name="amount" step="0.01" required>
            <button type="submit" name="deposit">Deposit</button>
        </form>

        <h2>Withdraw</h2>
        <form method="POST">
            <input type="number" name="amount" step="0.01" required>
            <button type="submit" name="withdraw">Withdraw</button>
        </form>


    <!-- Transfer Section -->
    
    <h2>Transfer Money</h2>
    <form action="process_transfer.php" method="POST">
        <input type="hidden" name="sender_wallet_id" value="<?= $wallet_id ?>">
        
        <div class="form-group">
            <label>Recipient Wallet Name:</label>
            <input type="text" name="recipient_wallet_name" required>
        </div>
        
        <div class="form-group">
            <label>Amount:</label>
            <input type="number" name="amount" step="0.01" min="0.01" required>
        </div>
        
        <button type="submit">Transfer</button>
    </form>

    <!-- Scheduled Payments (To be implemented later) -->
    <h2>Scheduled Payments</h2>
    
    <a href="scheduled_payments.php?wallet_name=<?= urlencode($wallet_name) ?>" class="specialbutton">  Scheduled Payments</a>

        <h2>Transaction History</h2>
        <a href="transaction_history.php?wallet_name=<?= urlencode($wallet_name) ?>" class="specialbutton">View Transaction History</a>
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
