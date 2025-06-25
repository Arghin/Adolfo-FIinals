<?php
session_start();
include 'db.php';

/* Access control ─ admin OR has can_create */
if (!isset($_SESSION['user']) ||
    ($_SESSION['user']['role'] !== 'admin' && !($_SESSION['user']['can_create'] ?? 0))
) {
    header("Location: login.php");
    exit;
}

$isAdmin = $_SESSION['user']['role'] === 'admin';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);   // ⚠ hash in production!
    $role     = $isAdmin ? $_POST['role'] : 'user';

    /* Permissions (Read forced ON) */
    $canCreate = isset($_POST['can_create']) ? 1 : 0;
    $canRead   = 1;
    $canEdit   = isset($_POST['can_edit'])   ? 1 : 0;
    $canDelete = isset($_POST['can_delete']) ? 1 : 0;

    if ($username && $password) {

        /* Duplicate-username check */
        $dup = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $dup->bind_param("s", $username);
        $dup->execute();
        if ($dup->get_result()->num_rows) {
            $error = "Username already exists.";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO users
                (username, password, role,
                 can_create, can_read, can_edit, can_delete,
                 created_at, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'active')
            ");
            $stmt->bind_param(
                "sssiiii",
                $username, $password, $role,
                $canCreate, $canRead, $canEdit, $canDelete
            );

            if ($stmt->execute()) {
                $success = "✅ User added successfully.";

                /* Activity log */
                addLog(
                    $conn,
                    $_SESSION['user']['id'],
                    $_SESSION['user']['username'],
                    "Created user: $username",
                    'success'
                );
            } else {
                $error = "Failed to add user.";
            }
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/aduse.css">
</head>
<body>

<div class="top-bar">
    <a href="manage_users.php"><i class="fas fa-arrow-left"></i> Back to Manage Users</a>
</div>

<form method="POST" class="add-user-form">
    <h2><i class="fas fa-user-plus"></i> Add User</h2>

    <?php if ($error):   ?><p class="msg error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="msg success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></p><?php endif; ?>

    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <!-- Role selector only for admins -->
    <?php if ($isAdmin): ?>
        <label>Role</label>
        <select name="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
    <?php else: ?>
        <input type="hidden" name="role" value="user">
    <?php endif; ?>

    <div class="permissions">
        <strong><i class="fas fa-key"></i> Permissions</strong><br><br>
        <label><input type="checkbox" name="can_create" value="1"> Create</label>

        <!-- Read always on -->
        <label><input type="checkbox" checked disabled> Read</label>

        <label><input type="checkbox" name="can_edit"   value="1"> Edit</label>
        <label><input type="checkbox" name="can_delete" value="1"> Delete</label>
    </div>

    <button class="btn" type="submit"><i class="fas fa-user-plus"></i> Add User</button>
</form>

</body>
</html>
