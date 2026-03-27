<?php
include("../../config/db.php");

$id = $_GET['id'];

// GET OLD RECORD
$stmt = $conn->prepare("SELECT * FROM packaging WHERE PackagingID=?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if ($row) {

    $productId = $row['ProductID'];
    $units = $row['QuantityProduced'];
    $processed = $row['ProcessedUsedKg'];

    // DELETE RECORD
    $conn->prepare("DELETE FROM packaging WHERE PackagingID=?")->execute([$id]);

    // RESTORE PROCESSED STOCK
    $conn->prepare("
        UPDATE processedhoneystock 
        SET QuantityAvailableKg = QuantityAvailableKg + ?
    ")->execute([$processed]);

    // REDUCE INVENTORY
    $conn->prepare("
        UPDATE inventory 
        SET QuantityAvailable = QuantityAvailable - ?
        WHERE ProductID=?
    ")->execute([$units, $productId]);
}

header("Location: index.php");