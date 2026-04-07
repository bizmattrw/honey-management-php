<?php
include("../config/db.php");
header('Content-Type: application/json');

$from = $_POST['from'] ?? '';
$to   = $_POST['to'] ?? '';

$whereSales = "WHERE 1=1";
$whereExpense = "WHERE 1=1";

/* DATE FILTER */
if(!empty($from) && !empty($to)){
    $whereSales .= " AND SaleDate BETWEEN :from AND :to";
    $whereExpense .= " AND ExpenseDate BETWEEN :from AND :to";
}

/* SALES (Revenue) */
$sqlSales = "SELECT COALESCE(SUM(TotalAmount),0) as revenue FROM sales $whereSales";
$stmt = $conn->prepare($sqlSales);

if($from && $to){
    $stmt->bindValue(':from',$from);
    $stmt->bindValue(':to',$to);
}

$stmt->execute();
$revenue = $stmt->fetch()['revenue'];

/* EXPENSES */
$sqlExp = "SELECT COALESCE(SUM(Amount),0) as expense FROM expenses $whereExpense";
$stmt = $conn->prepare($sqlExp);

if($from && $to){
    $stmt->bindValue(':from',$from);
    $stmt->bindValue(':to',$to);
}

$stmt->execute();
$expense = $stmt->fetch()['expense'];

/* PROFIT */
$profit = $revenue - $expense;

/* RETURN SIMPLE JSON */
echo json_encode([
    "data" => [[
        "revenue" => $revenue,
        "expense" => $expense,
        "profit" => $profit
    ]],
    "totalRevenue" => $revenue,
    "totalExpense" => $expense,
    "totalProfit" => $profit
]);