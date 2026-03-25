<?php
require 'config/db.php';

$province = $_GET['province'];

$stmt = $conn->prepare("SELECT * FROM districts WHERE ProvinceCode=?");
$stmt->execute([$province]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));