<?php
include("../config/db.php");

$from = $_POST['from'] ?? '';
$to   = $_POST['to'] ?? '';

$where = "WHERE 1=1";

if(!empty($from) && !empty($to)){
    $where .= " AND SaleDate BETWEEN '$from' AND '$to'";
}

// 📈 SALES TREND
$salesTrend = $conn->query("
SELECT SaleDate, SUM(TotalAmount) as total
FROM sales
$where
GROUP BY SaleDate
")->fetchAll();

// 💰 PAYMENT SUMMARY
$payments = $conn->query("
SELECT 
SUM(TotalAmount) as totalSales,
(SELECT IFNULL(SUM(AmountPaid),0) FROM payments) as totalPaid
FROM sales
$where
")->fetch();

// 🥧 STATUS
$status = $conn->query("
SELECT PaymentStatus, COUNT(*) as total
FROM sales
$where
GROUP BY PaymentStatus
")->fetchAll();

echo json_encode([
    "trend" => $salesTrend,
    "payments" => $payments,
    "status" => $status
]);