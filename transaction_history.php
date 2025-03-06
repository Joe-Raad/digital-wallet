
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user']['id'];
$wallet_name = $_GET['wallet_name'] ?? '';

// Verify wallet ownership
$stmt = $conn->prepare("SELECT wallet_name FROM wallet_profiles WHERE wallet_name = ? AND user_id = ?");
$stmt->bind_param("si", $wallet_name, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$wallet = $result->fetch_assoc();

if (!$wallet) {
    header('Location: view_wallets.php');
    exit;
}

// Handle filters
$type_filter = $_GET['type'] ?? '';
$date_filter = $_GET['date'] ?? '';

$query = "SELECT * FROM transaction_history WHERE wallet_name = ?";
$params = [$wallet_name];
$types = "s";

if (!empty($type_filter)) {
    $query .= " AND type = ?";
    $params[] = $type_filter;
    $types .= "s";
}

if (!empty($date_filter)) {
    $query .= " AND DATE(transaction_date) = ?";
    $params[] = $date_filter;
    $types .= "s";
}

$query .= " ORDER BY transaction_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$transactions = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .filters { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
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
        <h1>Transaction History - <?= htmlspecialchars($wallet_name) ?></h1>
        
        <div class="filters">
            <form method="GET">
                <input type="hidden" name="wallet_name" value="<?= htmlspecialchars($wallet_name) ?>">
                <select name="type">
                    <option value="">All Types</option>
                    <option value="deposit" <?= $type_filter === 'deposit' ? 'selected' : '' ?>>Deposit</option>
                    <option value="withdraw" <?= $type_filter === 'withdraw' ? 'selected' : '' ?>>Withdraw</option>
                    <option value="transfer" <?= $type_filter === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                </select>
                <input type="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">
                <button type="submit">Filter</button>
                <button type="button" onclick="downloadCSV()">Download CSV</button>
            </form>
        </div>

        <table id="transactionTable">
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Details</th>
            </tr>
            <?php while ($transaction = $transactions->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($transaction['transaction_date']) ?></td>
                <td><?= ucfirst(htmlspecialchars($transaction['type'])) ?></td>
                <td>$<?= number_format((float)$transaction['amount'], 2) ?></td>
                <td>
                    <?php if ($transaction['type'] === 'transfer'): ?>
                        To Wallet: <?= htmlspecialchars($transaction['recipient_wallet_name'] ?? '') ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        
        <div style="margin-top: 20px;">  
                <a href="view_wallets.php" class="specialbutton"> View Wallets Balances</a> 
        </div>

    </div>

    <script>
    function downloadCSV() {
        let table = document.getElementById("transactionTable");
        let rows = table.querySelectorAll("tr");
        let csv = [];

        rows.forEach(row => {
            let cols = row.querySelectorAll("td, th");
            let rowData = [];
            cols.forEach(col => rowData.push(col.innerText));
            csv.push(rowData.join(","));
        });

        let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "transaction_history.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>

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
