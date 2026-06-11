<?php
include("../config/db.php");

header('Content-Type: application/json');

// =======================
// INPUTS
// =======================
$from   = $_POST['from'] ?? '';
$to     = $_POST['to'] ?? '';
$search = $_POST['search']['value'] ?? '';

$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? $_POST['length'] : 10; 
$draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

// =======================
// HANDLE "ALL" OPTION
// =======================
$isAll = ($length === "all" || $length === "ALL" || $length == -1);

// =======================
// WHERE CLAUSE
// =======================
$where = "WHERE 1=1";
$params = [];

// DATE FILTER
if (!empty($from) && !empty($to)) {
    $where .= " AND r.DateReceived BETWEEN :from AND :to";
    $params[':from'] = $from;
    $params[':to']   = $to;
}

// SEARCH FILTER
if (!empty($search)) {
    $where .= " AND (
        s.Name LIKE :search OR
        s.phone LIKE :search OR
        d.DistrictName LIKE :search OR
        se.SectorName LIKE :search OR
        c.CellName LIKE :search OR
        v.VillageName LIKE :search OR
        r.BatchNo LIKE :search
    )";
    $params[':search'] = "%$search%";
}

// =======================
// TOTAL RECORDS
// =======================
$totalRecords = $conn->query("SELECT COUNT(*) FROM rawhoney")->fetchColumn();

// =======================
// FILTERED RECORDS
// =======================
$countSql = "
SELECT COUNT(*)
FROM rawhoney r
LEFT JOIN suppliers s ON r.SupplierID = s.SupplierID
LEFT JOIN districts d ON s.districtCode = d.DistrictCode
LEFT JOIN sectors se ON s.sectorCode = se.SectorCode
LEFT JOIN cells c ON s.cellCode = c.CellCode
LEFT JOIN villages v ON s.villageCode = v.VillageCode
$where
";

$stmt = $conn->prepare($countSql);
$stmt->execute($params);
$recordsFiltered = $stmt->fetchColumn();

// =======================
// MAIN QUERY
// =======================
$dataSql = "
SELECT r.BatchNo,
       r.QuantityKg,
       r.price,
       r.DateReceived,
       s.Name AS SupplierName,
       s.phone,
       d.DistrictName,
       se.SectorName,
       c.CellName,
       v.VillageName
FROM rawhoney r
LEFT JOIN suppliers s ON r.SupplierID = s.SupplierID
LEFT JOIN districts d ON s.districtCode = d.DistrictCode
LEFT JOIN sectors se ON s.sectorCode = se.SectorCode
LEFT JOIN cells c ON s.cellCode = c.CellCode
LEFT JOIN villages v ON s.villageCode = v.VillageCode
$where
";

// =======================
// APPLY PAGINATION
// =======================
if (!$isAll) {
    $dataSql .= " LIMIT :start, :length";
}

$stmt = $conn->prepare($dataSql);

// bind filters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// bind pagination
if (!$isAll) {
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
}

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =======================
// PROCESS DATA
// =======================
$data = [];
$totalQty = 0;
$totalAmount = 0;

foreach ($rows as $row) {

    $qty = $row['QuantityKg'] ?? 0;
    $price = $row['price'] ?? 0;
    $amount = $qty * $price;

    $totalQty += $qty;
    $totalAmount += $amount;

    $data[] = [
        "BatchNo"  => $row['BatchNo'],
        "supplier" => $row['SupplierName'],
        "phone"    => $row['phone'],
        "district" => $row['DistrictName'],
        "sector"   => $row['SectorName'],
        "cell"     => $row['CellName'],
        "village"  => $row['VillageName'],
        "qty"      => $qty,
        "price"    => $price,
        "amount"   => $amount,
        "date"     => $row['DateReceived']
    ];
}

// =======================
// RESPONSE
// =======================
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $recordsFiltered,
    "data" => $data,
    "totalQty" => $totalQty,
    "totalAmount" => number_format($totalAmount, 2)
]);

exit;