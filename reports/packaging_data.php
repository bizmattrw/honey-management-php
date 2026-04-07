<?php
include("../config/db.php");

$from = $_POST['from'] ?? '';
$to   = $_POST['to'] ?? '';
$search = $_POST['search']['value'] ?? '';

$where = "WHERE 1=1";

// DATE FILTER
if(!empty($from) && !empty($to)){
    $where .= " AND p.PackagingDate BETWEEN '$from' AND '$to'";
}

// SEARCH FILTER
if(!empty($search)){
    $where .= " AND (
        pr.Name LIKE '%$search%' OR
        pr.Size LIKE '%$search%' OR
        p.BatchNo LIKE '%$search%' OR
        p.QuantityProduced LIKE '%$search%' OR
        p.ProcessedUsedKg LIKE '%$search%' OR
        p.PackagingDate LIKE '%$search%'
    )";
}

// BASE QUERY
$query = "
SELECT p.*, pr.Name, pr.Size
FROM packaging p
LEFT JOIN products pr ON p.ProductID = pr.ProductID
$where
";

// TOTAL RECORDS
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
        "Size" => $row['Size'],
        "BatchNo" => $row['BatchNo'],
        "Qty" => $row['QuantityProduced'],
        "Used" => $row['ProcessedUsedKg'],
        "Date" => $row['PackagingDate']
    ];
}

// RETURN JSON
echo json_encode([
    "draw" => intval($_POST['draw']),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $result,
    "totalQty" => $totalQty,
    "totalUsed" => $totalUsed
]);