

<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Get verification status from user_profiles
$stmt = $conn->prepare("SELECT tier FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$status = 'pending';
if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
    $status = $profile['tier'];
}

// Redirect based on status
if ($status === 'verified') {
    header('Location: mainaccount_verifiedtier.php');
    exit;
} elseif ($status === 'unverified') {
    header('Location: mainaccount_unverifiedtier.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account Verification</title>
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

        <div class="container">
        <h1>Account Verification Status</h1>
        <div class="tier-container">
            <div class="tier verified">
                <h2>Verified Tier</h2>
                <ul>
                    <li>Full transaction capabilities</li> 
                    <!--  Added daily weekly monthly info that could be changed later    -->
                    <li>Daily Transaction Limit: $2000</li>
                    <li>Weekly Transaction Limit: $14000</li>
                    <li>Monthly Transaction Limit: $60,000</li>
                    <li>Priority customer support</li>
                    <li>Enhanced security features</li>
                    <li>Higher limits</li>
                </ul>
            </div>

            <div class="tier unverified">
                <h2>Standard Tier</h2>
                <ul>
                    <li>Basic transaction limits</li>
                    <!--  Added daily weekly monthly info that could be changed later    -->
                    <li>Daily Transaction Limit: $500</li>
                    <li>Weekly Transaction Limit: $3500</li>
                    <li>Daily Transaction Limit: $15000</li>
                    <li>Standard support response</li>
                    <li>Essential features only</li>
                    <li>Temporary access</li>
                </ul>
            </div>
        </div>

        <div class="status">
            <?php if ($status === 'pending'): ?>
                <p>Your verification request is under review. Admin approval required.</p>
                <p>Average processing time: 24-48 hours</p>
            <?php else: ?>
                <p>Your current status: <?= ucfirst($status) ?> Tier</p>
            <?php endif; ?>
        </div>
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
