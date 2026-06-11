<?php
include("../config/db.php");

// RECEIVE INPUTS
$from   = $_POST['from'] ?? '';
$to     = $_POST['to'] ?? '';
$search = $_POST['search']['value'] ?? '';
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$draw   = intval($_POST['draw'] ?? 1);

// BASE WHERE
$where = "WHERE 1=1";
$params = [];

// DATE FILTER
if (!empty($from) && !empty($to)) {
    $where .= " AND ProcessingDate BETWEEN :from AND :to";
    $params[':from'] = $from;
    $params[':to']   = $to;
}

// SEARCH FILTER
if (!empty($search)) {
    $where .= " AND (
        BatchNo LIKE :search OR
        InputQuantityKg LIKE :search OR
        OutputQuantityKg LIKE :search OR
        ProcessingDate LIKE :search
    )";
    $params[':search'] = "%$search%";
}

// TOTAL RECORDS (AFTER FILTER)
$countQuery = "SELECT COUNT(*) FROM processingbatch $where";
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$recordsFiltered = $stmt->fetchColumn();

// TOTAL RECORDS (WITHOUT FILTER)
$totalQuery = "SELECT COUNT(*) FROM processingbatch";
$stmtTotal = $conn->query($totalQuery);
$recordsTotal = $stmtTotal->fetchColumn();

// MAIN DATA QUERY WITH LIMIT
$dataQuery = "SELECT * FROM processingbatch $where LIMIT :start, :length";
$stmt = $conn->prepare($dataQuery);

// BIND PARAMS
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':length', $length, PDO::PARAM_INT);

$stmt->execute();
$data = $stmt->fetchAll();

// PREPARE RESPONSE DATA
$res = [];

$totalInput  = 0;
$totalOutput = 0;
$totalLoss   = 0;

foreach ($data as $r) {

    $input  = $r['InputQuantityKg'] ?? 0;
    $output = $r['OutputQuantityKg'] ?? 0;
    $loss   = $input - $output;

    $totalInput  += $input;
    $totalOutput += $output;
    $totalLoss   += $loss;

    $res[] = [
        "BatchNo" => $r['BatchNo'],
        "Input"   => $input,
        "Output"  => $output,
        "Loss"    => $loss,
        "Date"    => $r['ProcessingDate']
    ];
}

// RETURN JSON
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data" => $res,
    "totalInput" => $totalInput,
    "totalOutput" => $totalOutput,
    "totalLoss" => $totalLoss
]);