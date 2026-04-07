<?php
include("../../config/db.php");

$id = $_GET['id'];

/* GET RECORD */
$data = $conn->query("SELECT * FROM processingbatch WHERE BatchID=$id")->fetch();

if(!$data){
    header("Location:index.php");
    exit;
}

$batch = $data['BatchNo'];
$input = $data['InputQuantityKg'];
$output = $data['OutputQuantityKg'];

try{

$conn->beginTransaction();

/* RESTORE RAW STOCK */
$conn->prepare("
UPDATE rawhoneystock
SET QuantityAvailableKg = QuantityAvailableKg + ?
WHERE BatchNo = ?
")->execute([$input, $batch]);

/* REMOVE FROM PROCESSED STOCK */
$conn->prepare("
UPDATE processedhoneystock
SET QuantityAvailableKg = QuantityAvailableKg - ?
WHERE BatchNo = ?
")->execute([$output, $batch]);

/* DELETE RECORD */
$conn->prepare("DELETE FROM processingbatch WHERE BatchID=?")
     ->execute([$id]);

$conn->commit();

}catch(Exception $e){
$conn->rollBack();
}

header("Location:index.php");