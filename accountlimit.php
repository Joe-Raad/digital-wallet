
<!DOCTYPE html>
<html> 
</html>



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
    <style>
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .tier-container { display: flex; gap: 20px; margin-top: 30px; }
        .tier { flex: 1; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .verified { border-color: #4CAF50; background: #f8fff8; }
        .unverified { border-color: #ff9800; background: #fff8f0; }
        .status { margin-top: 15px; padding: 10px; text-align: center; }
    </style>
</head>
<body>
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
</body>
</html>