<?php
require 'config/db.php';

$district = $_GET['district'];

$stmt = $conn->prepare("SELECT * FROM sectors WHERE DistrictCode=?");
$stmt->execute([$district]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));