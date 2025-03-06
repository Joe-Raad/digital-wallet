


<?php // code to be able to upload files   
if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
}
?>

<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

include 'db.php';

// Fetch existing profile data
$user_id = $_SESSION['user']['id'];
$profile_data = [];
$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile_data = $result->fetch_assoc();
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container { max-width: 600px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; }
        .file-upload { margin: 10px 0; }
    </style>
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
        <a href="index.html" class="specialbutton" >Logout</a>
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
            <h1>Profile Management</h1>
            <form action="save_profile.php" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($profile_data['username'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>ID Document (PDF/Image):</label>
                    <input type="file" class="file-upload" name="id_document" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if (!empty($profile_data['id_document'])): ?>
                        <p>Current file: <?php echo basename($profile_data['id_document']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address"><?php echo htmlspecialchars($profile_data['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($profile_data['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Facebook Profile:</label>
                    <input type="url" name="facebook" value="<?php echo htmlspecialchars($profile_data['facebook'] ?? ''); ?>">
                </div>

                <button type="submit">Save Profile</button>
            </form>

            <div style="margin-top: 20px;">
                <a href="accountlimit.php" class="specialbutton">Account Tier Limit</a>
            </div>

            <div style="margin-top: 20px;">
                <a href="security.php" class="specialbutton">Reset Email/Password</a>
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