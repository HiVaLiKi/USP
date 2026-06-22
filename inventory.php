<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$where_clause = "";
if (isset($_GET['filter']) && $_GET['filter'] == 'critical') {
    $where_clause = "WHERE i.quantity <= i.critical_minimum";
}


$query = "SELECT i.id, i.name AS item_name, i.barcode_digits, i.quantity, i.critical_minimum, 
                 i.location_rack, i.location_shelf, f.name AS facility_name
          FROM Items i
          JOIN Facilities f ON i.facility_id = f.id
          $where_clause
          ORDER BY f.name, i.name ASC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PINI Inventory - Item List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="topbar">
        <div class="logo"><a href="index.php" style="text-decoration: none; color: inherit;"><span>PINI</span></a></div>
        <div class="search-bar">
            <form action="search.php" method="GET" style="margin: 0; padding: 0;">
                <input type="text" name="q" placeholder="Search by name or barcode..." required>
                <button type="submit" style="display: none;"></button> </form>
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
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </nav>

        <main class="content">
            <div class="page-header">
                <h1>Inventory List</h1>
                <div style="display: flex; gap: 10px;">
                    
                    <a href="inventory.php<?php echo isset($_GET['filter']) ? '' : '?filter=critical'; ?>" 
                       class="btn btn-outline" 
                       style="<?php echo isset($_GET['filter']) ? 'border-color: #dc3545; color: #dc3545; background-color: #fff;' : ''; ?>">
                        <?php echo isset($_GET['filter']) ? '✖ Clear Filter' : '⚠️ Show Critical Only'; ?>
                    </a>
                    
                    <a href="export.php" class="btn btn-outline">Export CSV</a>
                    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">+ Add New Item</a>
                </div>
            </div>

            <div class="card table-card">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Barcode</th>
                            <th>Product Name</th>
                            <th>Facility</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
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
                                    <td><a href="details.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: #0d6efd; font-weight: bold;"><?php echo htmlspecialchars($row['item_name']); ?></a></td>
                                    <td><?php echo htmlspecialchars($row['facility_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['location_rack'] . ' / ' . $row['location_shelf']); ?></td>
                                    <td>
                                        <span class="qty-badge <?php echo ($row['quantity'] <= $row['critical_minimum']) ? 'qty-low' : ''; ?>">
                                            <?php echo $row['quantity']; ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <button class="btn btn-sm btn-success" onclick="openStockModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['item_name'])); ?>', 'restock')">Restock</button>
                                        <button class="btn btn-sm btn-danger" onclick="openStockModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['item_name'])); ?>', 'consume')">Consume</button>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline" style="text-decoration:none;">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No items found in inventory.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
    <div id="quickActionModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modalTitle">Update Stock</h3>
                <span class="close-btn" onclick="closeStockModal()">&times;</span>
            </div>
            
            <p id="modalItemName" style="margin-bottom: 15px; color: #555;"></p>
            
            <form action="update_stock.php" method="POST">
                <input type="hidden" id="modalItemId" name="item_id">
                <input type="hidden" id="modalAction" name="action_type">
                
                <div style="margin-bottom: 20px;">
                    <label for="amount" style="display: block; margin-bottom: 5px; font-weight: bold;">Amount:</label>
                    <input type="number" id="amount" name="amount" min="1" value="1" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                
                <button type="submit" id="modalSubmitBtn" class="btn btn-primary" style="width: 100%;">Confirm Action</button>
            </form>
        </div>
    </div>

    <script>
        function openStockModal(itemId, itemName, action) {
            document.getElementById('quickActionModal').style.display = 'flex';
            document.getElementById('modalItemId').value = itemId;
            document.getElementById('modalAction').value = action;
            document.getElementById('modalItemName').innerText = "Product: " + itemName;
            
            if(action === 'restock') {
                document.getElementById('modalTitle').innerText = '🟢 Restock Item';
                document.getElementById('modalSubmitBtn').innerText = 'Add to Inventory';
                document.getElementById('modalSubmitBtn').style.backgroundColor = '#198754'; // Green
            } else {
                document.getElementById('modalTitle').innerText = '🔴 Consume Item';
                document.getElementById('modalSubmitBtn').innerText = 'Remove from Inventory';
                document.getElementById('modalSubmitBtn').style.backgroundColor = '#dc3545'; // Red
            }
        }

        function closeStockModal() {
            document.getElementById('quickActionModal').style.display = 'none';
        }

        // Close popup if user clicks the dark background outside the box
        window.onclick = function(event) {
            if (event.target == document.getElementById('quickActionModal')) {
                closeStockModal();
            }
        }
    </script>


</body>
</html>