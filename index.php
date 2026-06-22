<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$qty_res = $conn->query("SELECT SUM(quantity) AS total FROM Items");
$total_items = $qty_res->fetch_assoc()['total'] ?? 0;


$low_res = $conn->query("SELECT COUNT(*) AS low FROM Items WHERE quantity <= critical_minimum");
$low_stock = $low_res->fetch_assoc()['low'] ?? 0;


$activity_query = "SELECT h.action_type, h.quantity_change, i.name, h.timestamp 
                   FROM Item_History h 
                   JOIN Items i ON h.item_id = i.id 
                   ORDER BY h.timestamp DESC LIMIT 5";
$activities = $conn->query($activity_query);


$chart_query = "SELECT f.name AS facility_name, IFNULL(SUM(i.quantity), 0) AS total_stock 
                FROM Facilities f 
                LEFT JOIN Items i ON f.id = i.facility_id 
                GROUP BY f.id, f.name";
$chart_res = $conn->query($chart_query);

$facility_names = [];
$facility_stocks = [];

while($row = $chart_res->fetch_assoc()) {
    $facility_names[] = $row['facility_name'];
    $facility_stocks[] = $row['total_stock'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PINI Inventory - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li class="active"><a href="index.php">Dashboard</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="groups.php">Groups</a></li>
		<li><a href="locations.php">Locations</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </nav>

        <main class="content">
            <h1>Dashboard</h1>
            
            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Total Inventory Items</h3>
                    <div class="number"><?php echo $total_items; ?></div>
                </div>
                
                <a href="inventory.php?sort=status" class="stat-card" style="text-decoration: none; cursor: pointer; display: block; transition: transform 0.2s;">
                    <h3 style="color: #6c757d;">Low Stock Alerts</h3>
                    <div class="number warning">⚠️ <?php echo $global_alert_count; ?></div>
                </a>
            </div>

            <div class="dashboard-lower">
                <div class="card chart-card">
                    <h3>Stock per Facility</h3>
                    <div style="height: 250px; width: 100%;">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>

                <div class="card activity-card">
                    <h3>Recent Activity</h3>
                    <ul>
                        <?php if($activities->num_rows > 0): ?>
                            <?php while($act = $activities->fetch_assoc()): ?>
                                <li>
                                    <span class="act-icon">
                                        <?php echo ($act['action_type'] == 'restock' || $act['action_type'] == 'add') ? '🟢' : '🟠'; ?>
                                    </span>
                                    <div class="act-details">
                                        <strong><?php echo htmlspecialchars($act['name']); ?></strong> 
                                        was <?php echo htmlspecialchars($act['action_type']); ?>ed 
                                        (<?php echo $act['quantity_change'] > 0 ? '+'.$act['quantity_change'] : $act['quantity_change']; ?>)
                                    </div>
                                    <span class="act-time"><?php echo date('M d, H:i', strtotime($act['timestamp'])); ?></span>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li>No recent activity logged yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

        </main>
    </div>

    <script>
        const ctx = document.getElementById('stockChart').getContext('2d');
        
        // We use PHP to echo the arrays directly into JavaScript format using json_encode
        const chartLabels = <?php echo json_encode($facility_names); ?>;
        const chartData = <?php echo json_encode($facility_stocks); ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Total Items in Stock',
                    data: chartData,
                    backgroundColor: '#ff9800', // Matches the orange from your mockup
                    borderRadius: 4, // Slightly rounds the top of the bars
                    barThickness: 40 // Keeps bars looking solid
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Hides the legend to look cleaner, like the mockup
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0 // Ensures we don't get decimal numbers for items
                        }
                    },
                    x: {
                        grid: {
                            display: false // Removes vertical background lines for a cleaner look
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>