<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT i.*, f.name AS facility_name 
          FROM Items i 
          JOIN Facilities f ON i.facility_id = f.id 
          WHERE i.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Item not found. Please go back to the inventory list.");
}

$item = $result->fetch_assoc();
$is_low_stock = ($item['quantity'] <= $item['critical_minimum']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PINI Inventory - Item Details</title>
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
                <h1>Item Details</h1>
                <a href="inventory.php" class="btn btn-outline">← Back to Inventory</a>
            </div>

            <div class="card details-card">
                <div class="details-info">
                    <p class="meta-text">Barcode: <?php echo htmlspecialchars($item['barcode_digits']); ?></p>
                    <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                    
                    <div class="details-stats">
                        <p><strong>Facility:</strong> <?php echo htmlspecialchars($item['facility_name']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location_rack'] . ' / ' . $item['location_shelf']); ?></p>
                        <br>
                        <p><strong>Quantity:</strong> <span class="qty-badge <?php echo $is_low_stock ? 'qty-low' : ''; ?>" style="font-size: 18px;"><?php echo $item['quantity']; ?></span></p>
                        <p><strong>Critical Minimum:</strong> <?php echo $item['critical_minimum']; ?></p>
                    </div>

                    <?php if($is_low_stock): ?>
                    <div class="warning-box">
                        <strong>⚠️ Severe Low Stock</strong>
                        <p>This item has fallen to or below its critical minimum. Please restock immediately.</p>
                    </div>
                    <?php endif; ?>

                    <div class="details-actions">
                        <button class="btn btn-success" onclick="openStockModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>', 'restock')">Restock</button>
                        <button class="btn btn-danger" onclick="openStockModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>', 'consume')">Consume</button>
                        <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-outline" style="text-decoration:none;">Edit Details</a>
                    </div>
                </div>

                <div class="details-image-container">
                    <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             style="width: 100%; max-width: 300px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); object-fit: cover;">
                    <?php else: ?>
                        <div class="big-placeholder-image">
                            📦
                            <p>No Image Available</p>
                        </div>
                    <?php endif; ?>
                </div>
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
                <input type="hidden" name="redirect" value="details.php?id=<?php echo $item['id']; ?>">
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
                document.getElementById('modalSubmitBtn').style.backgroundColor = '#198754';
            } else {
                document.getElementById('modalTitle').innerText = '🔴 Consume Item';
                document.getElementById('modalSubmitBtn').innerText = 'Remove from Inventory';
                document.getElementById('modalSubmitBtn').style.backgroundColor = '#dc3545';
            }
        }
        function closeStockModal() { document.getElementById('quickActionModal').style.display = 'none'; }
        window.onclick = function(event) { if (event.target == document.getElementById('quickActionModal')) { closeStockModal(); } }
    </script>
</body>
</html>