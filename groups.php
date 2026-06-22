<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$query = "SELECT id, username, created_at FROM Users ORDER BY id ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PINI Inventory - Groups</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="topbar">
        <div class="logo"><a href="index.php" style="text-decoration: none; color: inherit;"><span>PINI</span></a></div>
        <div class="search-bar">
            <form action="search.php" method="GET" style="margin: 0; padding: 0;">
                <input type="text" name="q" placeholder="Search by name or barcode..." required>
                <button type="submit" style="display: none;"></button>
            </form>
        </div>
        <div class="user-menu">
            <div class="user-dropdown">
                <span class="user-btn">👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼</span>
                <div class="dropdown-content">
                    <a href="logout.php" style="color: #dc3545; font-weight: bold;">🚪 Sign Out</a>
                </div>
            </div>
            
            <a href="inventory.php?filter=critical" class="notification-wrapper">
                <span class="notification-bell" style="font-size: 20px;">🔔</span>
                <?php if($global_alert_count > 0): ?>
                    <span class="notification-badge"><?php echo $global_alert_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </header>

    <div class="workspace">
        
        <nav class="sidebar">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li class="active"><a href="groups.php">Groups</a></li>
		<li><a href="locations.php">Locations</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </nav>

        <main class="content">
            <div class="page-header">
                <h1>Group: Main Team</h1>
                <button class="btn btn-primary">+ Invite User</button>
            </div>

            <div class="card table-card">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role (Demo)</th>
                            <th>Email (Demo)</th>
                            <th>Join Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                    
                                    <td><span style="background: #e7f1ff; color: #0d6efd; padding: 3px 8px; border-radius: 12px; font-size: 12px;">Staff</span></td>
                                    <td style="color: #666;"><?php echo htmlspecialchars($row['username']); ?>@pini-demo.com</td>
                                    
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td class="actions-cell">
                                        <?php if($row['id'] == $_SESSION['user_id']): ?>
                                            <span style="color: #aaa; font-size: 13px; font-style: italic;">(You)</span>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline" style="color: #dc3545; border-color: #dc3545;" onclick="alert('In a full version, this would remove the user from the group!')">Evict</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>