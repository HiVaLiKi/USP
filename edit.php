<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['item_id']);
    $barcode = $_POST['barcode'];
    $name = $_POST['name'];
    $rack = $_POST['rack'];
    $shelf = $_POST['shelf'];
    $critical_min = intval($_POST['critical_min']);

    // --- Image Update Logic ---
    $new_image_path = null;
    // Check if a new file was actually uploaded
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === 0) {
        $target_dir = "uploads/";
        $file_name = time() . '_' . basename($_FILES["item_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
            $new_image_path = $target_file;
        }
    }


    if ($new_image_path) {
        $update_query = "UPDATE Items SET barcode_digits=?, name=?, location_rack=?, location_shelf=?, critical_minimum=?, image_path=? WHERE id=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssisi", $barcode, $name, $rack, $shelf, $critical_min, $new_image_path, $id);
    } else {
        $update_query = "UPDATE Items SET barcode_digits=?, name=?, location_rack=?, location_shelf=?, critical_minimum=? WHERE id=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssii", $barcode, $name, $rack, $shelf, $critical_min, $id);
    }
    
    if ($stmt->execute()) {
        // Log this edit in the history
        $log_query = "INSERT INTO Item_History (item_id, action_type, quantity_change) VALUES (?, 'edit', 0)";
        $log_stmt = $conn->prepare($log_query);
        $log_stmt->bind_param("i", $id);
        $log_stmt->execute();

        header("Location: inventory.php");
        exit();
    } else {
        $error = "Failed to update item.";
    }
}


$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM Items WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Item not found. Please go back to the inventory list.");
}

$item = $result->fetch_assoc();


$alert_query = $conn->query("SELECT COUNT(*) AS count FROM Items WHERE quantity <= critical_minimum");
$global_alert_count = $alert_query->fetch_assoc()['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PINI Inventory - Edit Item</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="topbar">
        <div class="logo"><a href="index.php" style="text-decoration: none; color: inherit;">PINI</a></div>
        <div class="search-bar">
            <form action="search.php" method="GET" style="margin: 0; padding: 0;">
                <input type="text" name="q" placeholder="Search by name or barcode..." required>
                <button type="submit" style="display: none;"></button>
            </form>
        </div>
        <div class="user-menu">
            <div class="user-dropdown">
                <span class="user-btn">👤 <?php echo htmlspecialchars($_SESSION['username']); ?> ▼</span>
                <div class="dropdown-content"><a href="logout.php" style="color: #dc3545; font-weight: bold;">🚪 Sign Out</a></div>
            </div>
            <a href="inventory.php?filter=critical" class="notification-wrapper">
                <span class="notification-bell" style="font-size: 20px;">🔔</span>
                <?php if($global_alert_count > 0): ?><span class="notification-badge"><?php echo $global_alert_count; ?></span><?php endif; ?>
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
                <h1>Edit Item: <?php echo htmlspecialchars($item['name']); ?></h1>
                <a href="inventory.php" class="btn btn-outline">← Back to Inventory</a>
            </div>

            <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>

            <div class="card edit-card">
                <div class="edit-form-container">
                    <form action="edit.php?id=<?php echo $item_id; ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        
                        <div class="form-group">
                            <label>Update Image (Optional)</label>
                            <input type="file" name="item_image" accept="image/png, image/jpeg, image/webp" style="padding: 5px;">
                            <small style="color: #888; display: block; margin-top: 5px;">Leave blank to keep the current image.</small>
                        </div>

                        <div class="form-group">
                            <label>Barcode</label>
                            <input type="text" name="barcode" value="<?php echo htmlspecialchars($item['barcode_digits']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Location (Rack & Shelf)</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="rack" value="<?php echo htmlspecialchars($item['location_rack']); ?>" placeholder="Rack" style="flex: 1;">
                                <input type="text" name="shelf" value="<?php echo htmlspecialchars($item['location_shelf']); ?>" placeholder="Shelf" style="flex: 1;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Critical Minimum Quantity</label>
                            <input type="number" name="critical_min" value="<?php echo $item['critical_minimum']; ?>" min="0" required>
                        </div>

                        <div style="margin-top: 30px; display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="inventory.php" class="btn btn-outline" style="text-decoration: none;">Cancel</a>
                        </div>
                    </form>
                </div>

                <div class="edit-preview-container">
                    <div class="preview-box">
                        <div style="margin-bottom: 20px;">
                            <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                     alt="Current Image" 
                                     style="width: 100%; max-width: 200px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); object-fit: cover;">
                            <?php else: ?>
                                <div style="font-size: 60px; color: #aaa;">📦</div>
                                <p style="color: #888; font-size: 14px; margin-top: 10px;">No image uploaded</p>
                            <?php endif; ?>
                        </div>
                        
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p style="color: #666; margin-top: 10px;">Current Stock: <strong><?php echo $item['quantity']; ?></strong></p>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>
</html>