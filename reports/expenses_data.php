<?php
include("../config/db.php");
header('Content-Type: application/json');

$from = $_POST['from'] ?? '';
$to = $_POST['to'] ?? '';
$category = $_POST['category'] ?? '';

$where = "WHERE 1=1";

if($from && $to){
    $where .= " AND ExpenseDate BETWEEN :from AND :to";
}

if($category){
    $where .= " AND Category = :category";
}

/* TOTAL COUNT */
$totalQuery = "SELECT COUNT(*) FROM expenses $where";
$stmt = $conn->prepare($totalQuery);

if($from && $to){
    $stmt->bindValue(':from',$from);
    $stmt->bindValue(':to',$to);
}
if($category){
    $stmt->bindValue(':category',$category);
}

$stmt->execute();
$records = $stmt->fetchColumn();

/* MAIN DATA */
$sql = "SELECT * FROM expenses $where ORDER BY ExpenseDate DESC LIMIT :start,:length";
$stmt = $conn->prepare($sql);

if($from && $to){
    $stmt->bindValue(':from',$from);
    $stmt->bindValue(':to',$to);
}
if($category){
    $stmt->bindValue(':category',$category);
}

$stmt->bindValue(':start',(int)$_POST['start'],PDO::PARAM_INT);
$stmt->bindValue(':length',(int)$_POST['length'],PDO::PARAM_INT);

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* TOTAL */
$totalSql = "SELECT SUM(Amount) as total FROM expenses $where";
$stmt = $conn->prepare($totalSql);

if($from && $to){
    $stmt->bindValue(':from',$from);
    $stmt->bindValue(':to',$to);
}
if($category){
    $stmt->bindValue(':category',$category);
}

$stmt->execute();
$total = $stmt->fetch()['total'] ?? 0;

$result = [];

foreach($data as $row){
    $result[] = [
        "title"=>$row['Title'],
        "category"=>$row['Category'],
        "amount"=>$row['Amount'],
        "date"=>$row['ExpenseDate'],
        "desc"=>$row['Description']
    ];
}

echo json_encode([
    "draw"=>intval($_POST['draw']),
    "recordsTotal"=>$records,
    "recordsFiltered"=>$records,
    "data"=>$result,
    "totalAmount"=>$total
]);