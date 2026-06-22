<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = null;

if ($search_query !== '') {

    $exact_stmt = $conn->prepare("SELECT id FROM Items WHERE barcode_digits = ?");
    $exact_stmt->bind_param("s", $search_query);
    $exact_stmt->execute();
    $exact_res = $exact_stmt->get_result();

    if ($exact_res->num_rows === 1) {
        $item = $exact_res->fetch_assoc();
        header("Location: details.php?id=" . $item['id']);
        exit();
    }

    $like_query = '%' . $search_query . '%';
    $search_sql = "SELECT i.id, i.name AS item_name, i.barcode_digits, i.quantity, i.critical_minimum, 
                          i.location_rack, i.location_shelf, f.name AS facility_name
                   FROM Items i
                   JOIN Facilities f ON i.facility_id = f.id
                   WHERE i.name LIKE ? OR i.barcode_digits LIKE ?
                   ORDER BY i.name ASC";
                   
    $stmt = $conn->prepare($search_sql);
    $stmt->bind_param("ss", $like_query, $like_query);
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PINI Inventory - Search Results</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="topbar">
        <div class="logo"><a href="index.php" style="text-decoration: none; color: inherit;"><span>PINI</span></a></div>
        <div class="search-bar">
            <form action="search.php" method="GET" style="margin: 0; padding: 0;">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search by name or barcode..." required>
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
                <li class="active"><a href="inventory.php">Inventory</a></li>
                <li><a href="groups.php">Groups</a></li>
		<li><a href="locations.php">Locations</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </nav>

        <main class="content">
            <div class="page-header">
                <h1>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>
                <a href="inventory.php" class="btn btn-outline">← Back to Inventory</a>
            </div>

            <div class="card table-card">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Barcode</th>
                            <th>Product Name</th>
                            <th>Facility</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($results && $results->num_rows > 0): ?>
                            <?php while($row = $results->fetch_assoc()): ?>
                                <tr>
                                    <td class="status-cell" style="text-align: center;">
                                        <?php if($row['quantity'] <= $row['critical_minimum']): ?>
                                            <span title="Low Stock!" class="warning-icon">⚠️</span>
                                            <div style="font-size: 11px; color: #dc3545; font-weight: bold; margin-top: 4px; white-space: nowrap;">
                                                Low Stock (<?php echo $row['critical_minimum']; ?>)
                                            </div>
                                        <?php else: ?>
                                            <span class="ok-icon">✔️</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['barcode_digits']); ?></td>
                                    <td>
                                        <a href="details.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: #0d6efd; font-weight: bold;">
                                            <?php echo htmlspecialchars($row['item_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['facility_name']); ?></td>
                                    <td>
                                        <span class="qty-badge <?php echo ($row['quantity'] <= $row['critical_minimum']) ? 'qty-low' : ''; ?>">
                                            <?php echo $row['quantity']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px;">
                                    <h3 style="color: #666;">No items found matching "<?php echo htmlspecialchars($search_query); ?>"</h3>
                                    <p style="color: #999;">Try searching for a different name or scanning another barcode.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>