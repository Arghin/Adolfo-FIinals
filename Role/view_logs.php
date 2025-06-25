<?php 
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Fetch logs ordered by newest first
$result = $conn->query("SELECT * FROM logs ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activity Logs</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #f8f9fa;
            padding: 30px;
            margin: 0;
        }

        .top-left {
            margin-bottom: 15px;
        }

        .top-left a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e7f1ff;
            padding: 8px 14px;
            border-radius: 6px;
        }

        .top-left a:hover {
            background: #d4e7ff;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #0d6efd;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        table {
            width: 95%;
            margin: auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #0d6efd;
            color: #fff;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .status-success {
            color: green;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-fail {
            color: red;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-denied {
            color: orange;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>

<div class="top-left">
    <a href="index.php"><i data-lucide="arrow-left"></i> Back to Dashboard</a>
</div>

<h2><i data-lucide="clipboard-list"></i> Activity Logs</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Action</th>
            <th>Status</th>
            <th>IP Address</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['action']) ?></td>
            <td>
                <?php
                    switch ($row['status']) {
                        case 'success':
                            echo '<span class="status-success"><i data-lucide="check-circle"></i> Success</span>';
                            break;
                        case 'fail':
                            echo '<span class="status-fail"><i data-lucide="x-circle"></i> Fail</span>';
                            break;
                        case 'denied':
                            echo '<span class="status-denied"><i data-lucide="ban"></i> Denied</span>';
                            break;
                        default:
                            echo htmlspecialchars($row['status']);
                    }
                ?>
            </td>
            <td><?= htmlspecialchars($row['ip_address']) ?></td>
            <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<script>
    lucide.createIcons();
</script>
</body>
</html>
