<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$isAdmin = $user['role'] === 'admin';
$canCreate = $user['can_create'] ?? 0;

// Only allow admin or users with create permission
if (!$isAdmin && !$canCreate) {
    echo "<p style='color:red; text-align:center;'>Access denied: You do not have permission to create roles.</p>";
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = trim($_POST['role_name']);

    $canCreateRole = isset($_POST['can_create']) ? 1 : 0;
    $canRead   = isset($_POST['can_read'])   ? 1 : 0;
    $canEdit   = isset($_POST['can_edit'])   ? 1 : 0;
    $canDelete = isset($_POST['can_delete']) ? 1 : 0;

    if (!empty($role)) {
        $stmt = $conn->prepare("INSERT INTO roles (role_name, can_create, can_read, can_edit, can_delete) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiii", $role, $canCreateRole, $canRead, $canEdit, $canDelete);
        $stmt->execute();
        header("Location: index.php");
        exit;
    } else {
        $error = "Role name is required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Role</title>
    <link rel="stylesheet" href="css/cre.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #198754;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
        }

        .permissions {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .permissions label {
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            padding: 6px 10px;
        }

        button {
            margin-top: 25px;
            background: #198754;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
        }

        .back-link {
            margin-top: 15px;
            text-align: center;
        }

        .back-link a {
            text-decoration: none;
            color: #555;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2><i class="fas fa-user-shield"></i> Create New Role</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="role_name"><i class="fas fa-id-badge"></i> Role Name</label>
        <input type="text" name="role_name" id="role_name" required>

        <div class="permissions">
            <label><input type="checkbox" name="can_create" value="1"> <i class="fas fa-plus-circle"></i> Create</label>
            <label><input type="checkbox" name="can_read" value="1"> <i class="fas fa-eye"></i> Read</label>
            <label><input type="checkbox" name="can_edit" value="1"> <i class="fas fa-edit"></i> Edit</label>
            <label><input type="checkbox" name="can_delete" value="1"> <i class="fas fa-trash-alt"></i> Delete</label>
        </div>

        <button type="submit"><i class="fas fa-save"></i> Create Role</button>
    </form>

    <div class="back-link">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Role List</a>
    </div>
</div>

</body>
</html>
