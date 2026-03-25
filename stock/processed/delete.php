<?php
include("../../config/db.php");

$id = $_GET['id'];

// GET RECORD
$stmt = $conn->prepare("SELECT * FROM processingbatch WHERE BatchID=?");
$stmt->execute([$id]);
$row = $stmt->fetch();

// DELETE
$conn->prepare("DELETE FROM processingbatch WHERE BatchID=?")->execute([$id]);

// REVERSE STOCK
$conn->prepare("
    UPDATE rawhoneystock 
    SET QuantityAvailableKg = QuantityAvailableKg + ?
")->execute([$row['InputQuantityKg']]);

$conn->prepare("
    UPDATE processedhoneystock 
    SET QuantityAvailableKg = QuantityAvailableKg - ?
")->execute([$row['OutputQuantityKg']]);

header("Location: index.php");