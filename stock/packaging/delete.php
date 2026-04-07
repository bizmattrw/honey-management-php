<?php

include("../../config/db.php");

$id = $_GET['id'];

/* GET RECORD */
$stmt = $conn->prepare("SELECT * FROM packaging WHERE PackagingID=?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$data){
    header("Location:index.php");
    exit;
}

$batch = $data['BatchNo'];
$product = $data['ProductID'];
$qty = $data['QuantityProduced'];
$used = $data['ProcessedUsedKg'];

try{

$conn->beginTransaction();

/* RESTORE PROCESSED STOCK */
$conn->prepare("
UPDATE processedhoneystock
SET QuantityAvailableKg = QuantityAvailableKg + ?
WHERE BatchNo=?
")->execute([$used, $batch]);

/* REDUCE INVENTORY */
$conn->prepare("
UPDATE inventory
SET QuantityAvailable = QuantityAvailable - ?
WHERE BatchNo=? AND ProductID=?
")->execute([$qty, $batch, $product]);

/* DELETE RECORD */
$conn->prepare("DELETE FROM packaging WHERE PackagingID=?")
     ->execute([$id]);

$conn->commit();

}catch(Exception $e){
$conn->rollBack();
}

header("Location:index.php");