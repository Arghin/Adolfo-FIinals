<?php
/*******************************************************************
 * edit_profile.php — self-service profile update for any user
 * Requires  : db.php   (for $conn + addLog helper)
 * Depends on: $_SESSION['user']  being set on login
 *******************************************************************/
session_start();
include 'db.php';

/* ─────────── Access guard ─────────── */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$uid            = (int)$_SESSION['user']['id'];
$originalName   = $_SESSION['user']['username'];
$success = $error = '';

/* ─────────── Handle POST submission ─────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $newPassword = trim($_POST['password']);  // optional

    /* Check duplicate username (excluding self) */
    $chk = $conn->prepare("SELECT id FROM users WHERE username = ? AND id <> ?");
    $chk->bind_param("si", $newUsername, $uid);
    $chk->execute();
    if ($chk->get_result()->num_rows) {
        $error = "Username already taken.";
    } else {
        /* Build dynamic SQL depending on password field */
        if ($newPassword !== '') {
            $sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $newUsername, $newPassword, $uid);
        } else {
            $sql = "UPDATE users SET username = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newUsername, $uid);
        }
        $stmt->execute();

        /* Update session */
        $_SESSION['user']['username'] = $newUsername;

        /* Log the action */
        addLog($conn, $uid, $newUsername, 'update_profile', 'success');

        $success = "Profile updated successfully.";
    }
}

/* ─────────── Fetch fresh user row for display ─────────── */
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit My Profile</title>
    <link rel="stylesheet" href="css/edit_profile.css">
    <!-- Modern Fonts + Lucide Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>

<style>
    * {
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }

    body {
        background: linear-gradient(to right, #e0f7fa, #e3f2fd);
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
    }

    form.profile-form {
        background: white;
        border-radius: 16px;
        padding: 30px 40px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        max-width: 400px;
        width: 100%;
        animation: fadeIn 0.5s ease-in-out;
    }

    form.profile-form h2 {
        font-size: 24px;
        color: #0d6efd;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
    }

    label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        font-weight: 600;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        margin-top: 6px;
        border: 1px solid #ccc;
        border-radius: 8px;
        transition: 0.3s;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        border-color: #0d6efd;
        outline: none;
    }

    button.btn {
        width: 100%;
        margin-top: 25px;
        background: #0d6efd;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.3s;
    }

    button.btn:hover {
        background: #084cd5;
    }

    .back-link {
        margin-top: 20px;
        text-align: center;
    }

    .back-link a {
        text-decoration: none;
        color: #0d6efd;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .msg {
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .msg.success {
        background: #d1e7dd;
        color: #0f5132;
    }

    .msg.error {
        background: #f8d7da;
        color: #842029;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>


    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<form method="POST" class="profile-form">
    <h2><i data-lucide="user-cog"></i> Edit My Profile</h2>

    <?php if ($error): ?>
        <p class="msg error"><i data-lucide="x-circle"></i> <?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p class="msg success"><i data-lucide="check-circle"></i> <?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <label><i data-lucide="user"></i> Username</label>
    <input type="text" name="username"
           value="<?= htmlspecialchars($current['username']) ?>" required>

    <label><i data-lucide="lock"></i> New Password
        <small>(leave blank to keep current)</small></label>
    <input type="password" name="password" placeholder="Enter new password">

    <button class="btn" type="submit"><i data-lucide="save"></i> Save Changes</button>

    <div class="back-link">
        <a href="index.php"><i data-lucide="arrow-left"></i> Back to Dashboard</a>
    </div>
</form>

<script>
    lucide.createIcons();
</script>

</body>
</html>
