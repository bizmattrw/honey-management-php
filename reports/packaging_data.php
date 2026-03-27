<?php
include("../config/db.php");

$from = $_POST['from'] ?? '';
$to   = $_POST['to'] ?? '';

$where = "WHERE 1=1";

if(!empty($from) && !empty($to)){
    $where .= " AND p.PackagingDate BETWEEN '$from' AND '$to'";
}

$query = "
SELECT p.*, pr.Name, pr.Size
FROM packaging p
LEFT JOIN products pr ON p.ProductID = pr.ProductID
$where
";

$totalData = $conn->query($query)->fetchAll();
$totalRecords = count($totalData);

// PAGINATION
$query .= " LIMIT ".$_POST['start'].",".$_POST['length'];
$data = $conn->query($query)->fetchAll();

$result = [];

$totalQty = 0;
$totalUsed = 0;

foreach($data as $row){

    $totalQty += $row['QuantityProduced'];
    $totalUsed += $row['ProcessedUsedKg'];

    $result[] = [
        "Product" => $row['Name'],
        "Size" => $row['Size'],   // ✅ NEW COLUMN
        "Qty" => $row['QuantityProduced'],
        "Used" => $row['ProcessedUsedKg'],
        "Date" => $row['PackagingDate']
    ];
}

echo json_encode([
    "draw" => intval($_POST['draw']),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $result,

    "totalQty" => $totalQty,
    "totalUsed" => $totalUsed
]);