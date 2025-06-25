<?php
session_start();
include 'db.php';

/* ── Admin-only guard ── */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

/* ── Validate ID ── */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: manage_users.php'); exit; }

/* ── Fetch user row ── */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) { echo 'User not found.'; exit; }

/* ── Handle form submit ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role     = $_POST['role'];
    $status   = $_POST['status'];
    $password = trim($_POST['password']);          // blank = keep current

    /* permissions – read forced ON */
    $can_create = isset($_POST['can_create']) ? 1 : 0;
    $can_read   = 1;
    $can_edit   = isset($_POST['can_edit'])   ? 1 : 0;
    $can_delete = isset($_POST['can_delete']) ? 1 : 0;

    if ($password !== '') {
        /* If hashing: $password = password_hash($password, PASSWORD_DEFAULT); */
        $sql = "
            UPDATE users
            SET username   = ?,
                role       = ?,
                status     = ?,
                can_create = ?,
                can_read   = ?,
                can_edit   = ?,
                can_delete = ?,
                password   = ?
            WHERE id = ?
        ";
        $upd = $conn->prepare($sql);
        $upd->bind_param(
            "sssiiii si",          // 9 params → 9 letters (no space inside!)
            $username,
            $role,
            $status,
            $can_create,
            $can_read,
            $can_edit,
            $can_delete,
            $password,
            $id
        );
    } else {
        $sql = "
            UPDATE users
            SET username   = ?,
                role       = ?,
                status     = ?,
                can_create = ?,
                can_read   = ?,
                can_edit   = ?,
                can_delete = ?
            WHERE id = ?
        ";
        $upd = $conn->prepare($sql);
        $upd->bind_param(
            "sssiiiii",            // 8 params → 8 letters
            $username,
            $role,
            $status,
            $can_create,
            $can_read,
            $can_edit,
            $can_delete,
            $id
        );
    }

    if ($upd->execute()) {
        header('Location: index.php?msg=updated');
        exit;
    }
    echo 'Error updating user.';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/edit_user.css">
</head>
<body>

<form method="POST">
    <h2><i class="fas fa-user-edit"></i> Edit User</h2>

    <label><i class="fas fa-user"></i> Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label><i class="fas fa-user-tag"></i> Role</label>
    <select name="role" required>
        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        <option value="user"  <?= $user['role']=='user' ?'selected':'' ?>>User</option>
    </select>

    <label><i class="fas fa-toggle-on"></i> Status</label>
    <select name="status" required>
        <option value="active"   <?= $user['status']=='active'  ?'selected':'' ?>>Active</option>
        <option value="inactive" <?= $user['status']=='inactive'?'selected':'' ?>>Inactive</option>
    </select>

    <label><i class="fas fa-lock"></i> New Password <small>(leave blank to keep current)</small></label>
    <input type="text" name="password" placeholder="Enter new password">

    <div class="permissions">
        <strong><i class="fas fa-key"></i> Permissions</strong><br><br>
        <div class="checkbox-group">
            <label><input type="checkbox" name="can_create" value="1" <?= $user['can_create']?'checked':'' ?>> Create</label>
            <label><input type="checkbox" disabled checked> Read</label>
            <label><input type="checkbox" name="can_edit" value="1" <?= $user['can_edit']?'checked':'' ?>> Edit</label>
            <label><input type="checkbox" name="can_delete" value="1" <?= $user['can_delete']?'checked':'' ?>> Delete</label>
        </div>
    </div>

    <button class="btn" type="submit"><i class="fas fa-save"></i> Save Changes</button>

    <div class="back-link">
        <a href="manage_users.php"><i class="fas fa-arrow-left"></i> Back to User List</a>
    </div>
</form>

</body>
</html>
