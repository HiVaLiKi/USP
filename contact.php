<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PINI Inventory - Contact Us</title>
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
                <li><a href="groups.php">Groups</a></li>
		<li><a href="locations.php">Locations</a></li>
                <li class="active"><a href="contact.php">Contact Us</a></li>
		<li><a href="#">Settings</a></li>
            </ul>
        </nav>

        <main class="content">
            <div class="page-header">
                <h1>Contact Us</h1>
            </div>

            <div class="card edit-card" style="align-items: flex-start;">
                
                <div class="edit-form-container" style="flex: 1;">
                    <h2 style="color: #0d6efd; margin-bottom: 15px;">We are the PINI Team</h2>
                    <p style="color: #555; line-height: 1.6; margin-bottom: 25px; font-size: 16px;">
                        We produce cutting-edge software for warehouse item tracking, inventory management, and seamless logistics. 
                        Our goal is to help your business eliminate stock-outs, track movements in real-time, and scale effortlessly.
                    </p>

                    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #0d6efd;">
                        <h3 style="margin-bottom: 15px; font-size: 18px; color: #333;">Get in Touch</h3>
                        
                        <p style="margin-bottom: 10px; color: #444;">
                            <strong style="display: inline-block; width: 70px;">Phone:</strong> 
                            +359 88 123 4567
                        </p>
                        <p style="margin-bottom: 10px; color: #444;">
                            <strong style="display: inline-block; width: 70px;">Email:</strong> 
                            <a href="mailto:hello@pini-demo.com" style="color: #0d6efd; text-decoration: none;">hello@pini-demo.com</a>
                        </p>
                        <p style="margin-bottom: 10px; color: #444;">
                            <strong style="display: inline-block; width: 70px;">Hours:</strong> 
                            Mon - Fri, 9:00 AM - 6:00 PM
                        </p>
                    </div>
                </div>

                <div class="edit-preview-container" style="flex: 1; padding-left: 40px;">
                    <h3 style="margin-bottom: 15px; color: #333;">Find Us Here</h3>
                    
                    <div style="width: 100%; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2933.420133848251!2d23.326958175294497!3d42.673642015234!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40aa85ac6322b7eb%3A0xf6d9bef18b6bfaff!2sg.k.%20Lozenets%2C%20Blvd%20%22James%20Bourchier%22%205%2C%201164%20Sofia!5e0!3m2!1sen!2sbg!4v1781908405439!5m2!1sen!2sbg" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <p style="text-align: center; color: #888; font-size: 14px; margin-top: 15px;">
                        PINI Headquarters, Sofia, Bulgaria
                    </p>
                </div>

            </div>

        </main>
    </div>

</body>
</html>