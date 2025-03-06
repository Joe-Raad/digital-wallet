
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user']['id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create New Wallet</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .wallet-form { max-width: 400px; margin: 50px auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; }
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
        <h1>Create New Wallet</h1>
        <form action="save_wallet.php" method="POST">
            <div class="form-group">
                <label>Wallet Name:</label>
                <input type="text" name="wallet_name" required>
            </div>
            
            <div class="form-group">
                <label>Wallet Type:</label>
                <select name="wallet_type" required>
                    <option value="personal">Personal</option>
                    <option value="business">Business</option>
                    <option value="savings">Savings</option>
                </select>
            </div>
            
            <button type="submit">Create Wallet</button>
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