<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['facility_name'])) {
    $warehouse_id = intval($_POST['warehouse_id']);
    $facility_name = $_POST['facility_name'];

    $insert_query = "INSERT INTO Facilities (warehouse_id, name) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("is", $warehouse_id, $facility_name);
    $stmt->execute();
    

    header("Location: locations.php");
    exit();
}



$warehouses = $conn->query("SELECT id, name FROM Warehouses ORDER BY name ASC");


$facilities_query = "SELECT f.id, f.name AS facility_name, w.name AS warehouse_name 
                     FROM Facilities f 
                     JOIN Warehouses w ON f.warehouse_id = w.id 
                     ORDER BY w.name, f.name ASC";
$facilities = $conn->query($facilities_query);


$alert_query = $conn->query("SELECT COUNT(*) AS count FROM Items WHERE quantity <= critical_minimum");
$global_alert_count = $alert_query->fetch_assoc()['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PINI Inventory - Locations</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="topbar">
        <div class="logo">
            <a href="index.php" style="text-decoration: none; color: inherit;">PINI</a>
        </div>
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
                <li><a href="groups.php">Groups</a></li>
		<li class="active"><a href="locations.php">Locations</a></li>
                <li><a href="contact.php">Contact Us</a></li>
		<li><a href="#">Settings</a></li>
            </ul>
        </nav>

        <main class="content">
            <div class="page-header">
                <h1>Warehouse & Facilities</h1>
            </div>

            <div class="card edit-card" style="align-items: flex-start; gap: 40px;">
                
                <div class="edit-form-container" style="flex: 1; background: #f8f9fa; padding: 25px; border-radius: 8px;">
                    <h3 style="margin-bottom: 20px; color: #2c3e50;">+ Add New Facility</h3>
                    
                    <form action="locations.php" method="POST">
                        <div class="form-group">
                            <label>Parent Warehouse</label>
                            <select name="warehouse_id" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                                <?php while($wh = $warehouses->fetch_assoc()): ?>
                                    <option value="<?php echo $wh['id']; ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Facility / Room Name</label>
                            <input type="text" name="facility_name" placeholder="e.g., Cold Storage B" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Create Facility</button>
                    </form>
                </div>

                <div class="edit-preview-container" style="flex: 2; border-left: none; padding-left: 0;">
                    <h3 style="margin-bottom: 20px; color: #2c3e50;">Current Hierarchy</h3>
                    
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Warehouse</th>
                                <th>Facility (Room)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($facilities->num_rows > 0): ?>
                                <?php while($fac = $facilities->fetch_assoc()): ?>
                                    <tr>
                                        <td style="color: #888;">#<?php echo $fac['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($fac['warehouse_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($fac['facility_name']); ?></td>
                                        <td><span class="ok-icon" style="font-size: 14px;">✔️ Active</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">No facilities found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </main>
    </div>

</body>
</html>