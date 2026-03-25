<?php
require 'config/db.php';

$sector = $_GET['sector'];

$stmt = $conn->prepare("SELECT * FROM cells WHERE SectorCode=?");
$stmt->execute([$sector]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));