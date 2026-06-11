<?php
include("../config/db.php");

header('Content-Type: application/json');

$from = $_POST['from'] ?? '';
$to   = $_POST['to'] ?? '';

$params = [];
$where = "WHERE 1=1";

if($from && $to){
    $where .= " AND Date BETWEEN :from AND :to";
    $params[':from'] = $from;
    $params[':to'] = $to;
}

/* ======================
   TOTALS
====================== */

// SALES
$stmt = $conn->prepare("SELECT COALESCE(SUM(TotalAmount),0) FROM sales WHERE 1=1 ".($from?" AND SaleDate BETWEEN :from AND :to":""));
$stmt->execute($params);
$revenue = $stmt->fetchColumn();

// EXPENSES
$stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) FROM expenses WHERE 1=1 ".($from?" AND ExpenseDate BETWEEN :from AND :to":""));
$stmt->execute($params);
$expense = $stmt->fetchColumn();

// PURCHASES
$stmt = $conn->prepare("SELECT COALESCE(SUM(QuantityKg * price),0) FROM rawhoney WHERE 1=1 ".($from?" AND DateReceived BETWEEN :from AND :to":""));
$stmt->execute($params);
$purchases = $stmt->fetchColumn();

// VAT & TAX
$vat = $revenue * 0.18;
$tax = $revenue * 0.03;

// PROFIT
$profit = $revenue - $expense - $purchases - $vat - $tax;

/* ======================
   MONTHLY TREND
====================== */

$trendLabels = [];
$trendData = [];

$q = "
SELECT 
    DATE_FORMAT(SaleDate,'%Y-%m') as month,
    SUM(TotalAmount) as revenue
FROM sales
GROUP BY month
ORDER BY month
";

$res = $conn->query($q)->fetchAll();

foreach($res as $row){
    $trendLabels[] = $row['month'];

    $monthRevenue = $row['revenue'];
    $monthProfit = $monthRevenue - ($monthRevenue*0.18) - ($monthRevenue*0.03);

    $trendData[] = $monthProfit;
}

/* ======================
   RESPONSE
====================== */

echo json_encode([
    "summary" => [
        "revenue" => $revenue,
        "expense" => $expense,
        "purchases" => $purchases,
        "vat" => $vat,
        "tax" => $tax,
        "profit" => $profit
    ],
    "trend" => [
        "labels" => $trendLabels,
        "data" => $trendData
    ]
]);