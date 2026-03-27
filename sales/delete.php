<?php
include("../config/db.php");

$id = $_GET['id'];

// RESTORE INVENTORY
$items = $conn->query("SELECT * FROM saledetails WHERE SaleID=$id")->fetchAll();

foreach($items as $i){
    $conn->query("
    UPDATE inventory 
    SET QuantityAvailable = QuantityAvailable + {$i['Quantity']}
    WHERE ProductID={$i['ProductID']}
    ");
}

// DELETE
$conn->query("DELETE FROM payments WHERE SaleID=$id");
$conn->query("DELETE FROM saledetails WHERE SaleID=$id");
$conn->query("DELETE FROM sales WHERE SaleID=$id");

header("Location: index.php");