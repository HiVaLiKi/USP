<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$facilities_query = "SELECT id, name FROM Facilities ORDER BY name ASC";
$facilities_result = $conn->query($facilities_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $barcode = $_POST['barcode'];
    $facility_id = intval($_POST['facility_id']);
    $rack = $_POST['rack'];
    $shelf = $_POST['shelf'];
    $quantity = intval($_POST['quantity']);
    $critical_min = intval($_POST['critical_min']);
    
    $image_path = null;
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === 0) {
        $target_dir = "uploads/";
        // Generate a unique file name using the current timestamp so files don't overwrite each other
        $file_name = time() . '_' . basename($_FILES["item_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        // Move the file from the temporary upload path to our uploads folder
        if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    $insert_query = "INSERT INTO Items (facility_id, name, barcode_digits, quantity, critical_minimum, location_rack, location_shelf, image_path) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ississss", $facility_id, $name, $barcode, $quantity, $critical_min, $rack, $shelf, $image_path);
    
    if ($stmt->execute()) {
        $new_item_id = $stmt->insert_id;
        $log_query = "INSERT INTO Item_History (item_id, action_type, quantity_change) VALUES (?, 'add', ?)";
        $log_stmt = $conn->prepare($log_query);
        $log_stmt->bind_param("ii", $new_item_id, $quantity);
        $log_stmt->execute();

        header("Location: inventory.php");
        exit();
    } else {
        $error = "Failed to add new item.";
    }
}

$alert_query = $conn->query("SELECT COUNT(*) AS count FROM Items WHERE quantity <= critical_minimum");
$global_alert_count = $alert_query->fetch_assoc()['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PINI Inventory - Add Item</title>
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
                <h1>Add New Inventory Item</h1>
                <a href="inventory.php" class="btn btn-outline">← Back to Inventory</a>
            </div>

            <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>

            <div class="card edit-card">
                <div class="edit-form-container" style="flex: 1; max-width: 600px;">
                    <form action="add.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="form-group">
                            <label>Product Image</label>
                            <input type="file" name="item_image" accept="image/png, image/jpeg, image/webp" style="padding: 5px;">
                        </div>

                        <div class="form-group"><label>Product Name</label><input type="text" name="name" required></div>
                        <div class="form-group"><label>Barcode</label><input type="text" name="barcode" required></div>
                        <div class="form-group">
                            <label>Assign to Facility</label>
                            <select name="facility_id" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="" disabled selected>Select a Facility...</option>
                                <?php while($fac = $facilities_result->fetch_assoc()): ?>
                                    <option value="<?php echo $fac['id']; ?>"><?php echo htmlspecialchars($fac['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Location (Rack & Shelf)</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="rack" placeholder="Rack" style="flex: 1;">
                                <input type="text" name="shelf" placeholder="Shelf" style="flex: 1;">
                            </div>
                        </div>
                        <div class="form-group" style="display: flex; gap: 20px;">
                            <div style="flex: 1;"><label>Starting Quantity</label><input type="number" name="quantity" value="0" min="0" required></div>
                            <div style="flex: 1;"><label>Critical Minimum</label><input type="number" name="critical_min" value="5" min="0" required></div>
                        </div>

                        <div style="margin-top: 30px; display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary">Add Item</button>
                            <a href="inventory.php" class="btn btn-outline" style="text-decoration: none;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>