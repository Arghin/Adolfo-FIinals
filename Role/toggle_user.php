<?php
session_start();
include 'db.php';

/* ─── Admin-only guard ─── */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: manage_users.php'); exit; }

/* Fetch current status + role */
$stmt = $conn->prepare("SELECT status, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
if (!$info) { header('Location: manage_users.php'); exit; }

/* Optional: never toggle another admin */
if ($info['role'] === 'admin') {
    header('Location: manage_users.php?err=admin_protected');
    exit;
}

$wasActive = ($info['status'] === 'active');
$newStatus = $wasActive ? 'inactive' : 'active';

/* ─── Flip status
       + if we’re turning ON, refresh last_login ─── */
if ($newStatus === 'active') {
    $upd = $conn->prepare("
        UPDATE users
        SET status     = 'active',
            last_login = NOW()          -- ← this keeps auto-inactive from firing
        WHERE id = ?
    ");
} else {
    $upd = $conn->prepare("
        UPDATE users
        SET status = 'inactive'
        WHERE id = ?
    ");
}
$upd->bind_param("i", $id);
$upd->execute();

/* Log it */
addLog(
    $conn,
    $_SESSION['user']['id'],
    $_SESSION['user']['username'],
    "Toggled user id $id to $newStatus",
    'success'
);

header('Location: manage_users.php');
exit;
?>
