<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = intval($_POST['item_id']);
    $action_type = $_POST['action_type']; // 'restock' or 'consume'
    $amount = intval($_POST['amount']);

    if ($amount > 0) {

        if ($action_type == 'consume') {
            $amount_change = -$amount; // Negative number for consumption
        } else {
            $amount_change = $amount;  // Positive number for restock
        }


        $update_query = "UPDATE Items SET quantity = quantity + ? WHERE id = ?";
        $stmt1 = $conn->prepare($update_query);
        $stmt1->bind_param("ii", $amount_change, $item_id);
        $stmt1->execute();


        $history_query = "INSERT INTO Item_History (item_id, action_type, quantity_change) VALUES (?, ?, ?)";
        $stmt2 = $conn->prepare($history_query);
        $stmt2->bind_param("isi", $item_id, $action_type, $amount_change);
        $stmt2->execute();
    }
    

    if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
        header("Location: " . $_POST['redirect']);
    } else {
        header("Location: inventory.php");
    }
    exit();
}
?>