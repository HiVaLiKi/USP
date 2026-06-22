<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_export.csv');


$output = fopen('php://output', 'w');


fputcsv($output, array('Status', 'Barcode', 'Product Name', 'Facility', 'Location', 'Quantity', 'Critical Minimum'));


$query = "SELECT i.barcode_digits, i.name, f.name AS facility_name, 
                 i.location_rack, i.location_shelf, i.quantity, i.critical_minimum
          FROM Items i
          JOIN Facilities f ON i.facility_id = f.id
          ORDER BY f.name, i.name ASC";
          
$result = $conn->query($query);


while ($row = $result->fetch_assoc()) {

    $status = ($row['quantity'] <= $row['critical_minimum']) ? 'Low Stock' : 'OK';
    $location = $row['location_rack'] . ' / ' . $row['location_shelf'];
    

    fputcsv($output, array(
        $status,
        $row['barcode_digits'],
        $row['name'],
        $row['facility_name'],
        $location,
        $row['quantity'],
        $row['critical_minimum']
    ));
}


fclose($output);
exit();
?>