<?php
require 'config/db.php';

$cell = $_GET['cell'];

$stmt = $conn->prepare("SELECT * FROM villages WHERE CellCode=?");
$stmt->execute([$cell]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));