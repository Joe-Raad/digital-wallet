
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

include 'db.php';

// Fetch current user data
$user_id = $_SESSION['user']['id'];
$current_email = $_SESSION['user']['email'];

// Get current email from database
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$current_email = $user_data['email'];
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Security Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="header">
        <div class="menu-container">
            <button class="menu-button">☰</button>
            <div class="dropdown-menu">
                <button class="dropdown-item"><a href="index.html">Login</a></button>
                <button class="dropdown-item"><a href="index.html">Signup</a></button>
                <button class="dropdown-item"><a href="privacy.html">Privacy rules</a></button>
                <button class="dropdown-item"><a href="profile.php">Profile</a></button>
                <button class="dropdown-item"><a href="security.php">Security credentials</a></button>
                <button class="dropdown-item"><a href="create_wallet.php">Create Wallet</a></button>
                <button class="dropdown-item"><a href="view_wallets.php">View Wallet</a></button>
                <button class="dropdown-item"><a href="transaction_history.php">Transaction History</a></button>
                
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
        <h1>Security Settings</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Settings updated successfully!</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="error"><?php echo urldecode($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="update_security.php" method="POST">
            <div class="form-group">
                <label>Email Address:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required>
            </div>

            <div class="form-group">
                <label>New Password (leave blank to keep current):</label>
                <input type="password" name="password" placeholder="Enter new password">
            </div>

            <button type="submit">Update Security Settings</button>
        </form>
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