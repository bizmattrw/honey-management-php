<?php

include("../../config/db.php");

$id = $_GET['id'];

// GET RECORD
$stmt = $conn->prepare("SELECT QuantityKg FROM rawhoney WHERE RawHoneyID=?");
$stmt->execute([$id]);
$row = $stmt->fetch();

$qty = $row['QuantityKg'];

// DELETE RECORD
$conn->prepare("DELETE FROM rawhoney WHERE RawHoneyID=?")->execute([$id]);

// UPDATE STOCK (SUBTRACT)
$conn->prepare("
    UPDATE rawhoneystock 
    SET QuantityAvailableKg = QuantityAvailableKg - ?
")->execute([$qty]);

header("Location: index.php");