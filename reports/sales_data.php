<?php
include("../config/db.php");

$draw = $_POST['draw'];
$start = $_POST['start'];
$length = $_POST['length'];
$search = $_POST['search']['value'];

$from = $_POST['from'] ?? '';
$to   = $_POST['to'] ?? '';

// BASE QUERY
$query = "
SELECT s.SaleID, s.SaleDate, s.TotalAmount, s.PaymentStatus,
       c.Name,
       IFNULL(SUM(p.AmountPaid),0) as PaidAmount
FROM sales s
LEFT JOIN customers c ON s.CustomerID = c.CustomerID
LEFT JOIN payments p ON s.SaleID = p.SaleID
WHERE 1=1
";

// 🔥 DATE FILTER
if(!empty($from) && !empty($to)){
    $query .= " AND s.SaleDate BETWEEN '$from' AND '$to' ";
}

// 🔍 SEARCH
if($search != ''){
    $query .= " AND (c.Name LIKE '%$search%' OR s.SaleID LIKE '%$search%') ";
}

$query .= " GROUP BY s.SaleID";

// TOTAL
$totalData = $conn->query($query)->fetchAll();
$totalRecords = count($totalData);

// PAGINATION
$query .= " LIMIT $start, $length";

$data = $conn->query($query)->fetchAll();

$result = [];

$totalSales = 0;
$totalPaid = 0;
$totalBalance = 0;

foreach($data as $row){

    $balance = $row['TotalAmount'] - $row['PaidAmount'];

    $totalSales += $row['TotalAmount'];
    $totalPaid += $row['PaidAmount'];
    $totalBalance += $balance;

    $result[] = [
        "SaleID" => $row['SaleID'],
        "Name" => $row['Name'],
        "TotalAmount" => number_format($row['TotalAmount'],2),
        "PaidAmount" => number_format($row['PaidAmount'],2),
        "Balance" => number_format($balance,2),
        "PaymentStatus" => $row['PaymentStatus'],
        "SaleDate" => $row['SaleDate']
    ];
}

echo json_encode([
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $result,
    "totalSales" => number_format($totalSales,2),
    "totalPaid" => number_format($totalPaid,2),
    "totalBalance" => number_format($totalBalance,2)
]);