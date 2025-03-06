
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: admin.html');
    exit;
}

include 'db.php';

// Get all verification requests
$stmt = $conn->prepare("SELECT u.id, u.email, up.tier, up.verification_requested_at 
                       FROM users u
                       LEFT JOIN user_profiles up ON u.id = up.user_id
                       WHERE up.tier = 'pending' OR up.tier IS NULL");
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Verification Approval</title>
</head>
<body>
    <h1>Pending Verification Requests</h1>
    <table border="1">
        <tr>
            <th>User ID</th>
            <th>Email</th>
            <th>Request Date</th>
            <th>Current Tier</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($requests as $request): ?>
        <tr>
            <td><?= $request['id'] ?></td>
            <td><?= htmlspecialchars($request['email']) ?></td>
            <td><?= $request['verification_requested_at'] ?? 'Not requested' ?></td>
            <td><?= $request['tier'] ?? 'pending' ?></td>
            <td>
                <form action="admin_process_verification.php" method="POST">
                    <input type="hidden" name="user_id" value="<?= $request['id'] ?>">
                    <select name="tier">
                        <option value="verified">Verified</option>
                        <option value="unverified">Unverified</option>
                    </select>
                    <button type="submit">Update Tier</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>