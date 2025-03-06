

<?php    ///////////////////////////////////////
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

// Debug: Check session data
error_log("Session User ID: " . ($_SESSION['user']['id'] ?? 'NOT SET'));

$user_id = $_SESSION['user']['id'];

// Debug: Verify connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get wallets with error handling
try {
    $stmt = $conn->prepare("SELECT * FROM wallet_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    error_log("Found " . $result->num_rows . " wallets for user $user_id");
    
} catch (Exception $e) {
    die("Error fetching wallets: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Your Wallets</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .wallets-table { 
            width: 100%; 
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        .wallets-table th, .wallets-table td { 
            padding: 12px 15px; 
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .wallets-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
        }
        .wallets-table tr:hover {
            background-color: #f5f5f5;
        }
        .btn-manage {
            padding: 6px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-manage:hover {
            background-color: #0056b3;
        }
        .no-wallets {
            padding: 20px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 4px;
            margin-top: 20px;
        }
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
        <h1>Your Wallets</h1>
        
        <?php if ($result->num_rows > 0): ?>
            <table class="wallets-table">
                <thead>
                    <tr>
                        <th>Wallet Name</th>
                        <th>Type</th>
                        <th>Balance</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($wallet = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($wallet['wallet_name']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($wallet['wallet_type'])) ?></td>
                        <td>$<?= number_format((float)$wallet['balance'], 2) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($wallet['created_at'])) ?></td>
                        <td>
                            <a href="wallet_management.php?wallet_id=<?= $wallet['wallet_id'] ?>" 
                               class="btn-manage">
                                Manage
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-wallets">
                <p>No wallets found. You can create your first wallet using the button below.</p>
                <a href="create_wallet.php" style="color: #007bff; text-decoration: none;">
                    Create New Wallet →
                </a>
            </div>
        <?php endif; ?>
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
