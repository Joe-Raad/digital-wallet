

<?php
session_start();
require 'db.php';

// Verify admin authentication
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

// Process verification requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action']; // 'approve' or 'reject'

    try {
        $conn->begin_transaction();
        
        if ($action === 'approve') {
            // Update user verification status
            $stmt = $conn->prepare("UPDATE user_profiles 
                                   SET tier = 'verified', verification_approved_at = NOW() 
                                   WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Optional: Send notification/email
        } elseif ($action === 'reject') {
            // Update verification status
            $stmt = $conn->prepare("UPDATE user_profiles 
                                   SET tier = 'unverified' 
                                   WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        
        $conn->commit();
        $_SESSION['admin_message'] = "User verification $action successfully!";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['admin_error'] = "Error processing request: " . $e->getMessage();
    }
    
    header("Location: admin_dashboard.php");
    exit;
}

// If reached without POST request
header("Location: admin_dashboard.php");
exit;
?>