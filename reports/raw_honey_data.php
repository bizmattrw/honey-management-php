<?php
include("../config/db.php");

header('Content-Type: application/json');

$from = $_POST['from'] ?? '';
$to   = $_POST['to'] ?? '';
$search = $_POST['search']['value'] ?? '';

$where = "WHERE 1=1";

/* DATE FILTER */
if (!empty($from) && !empty($to)) {
    $where .= " AND r.DateReceived BETWEEN :from AND :to";
}

/* SEARCH FILTER */
if (!empty($search)) {
    $where .= " AND (
        s.Name LIKE :search OR
        s.phone LIKE :search OR
        d.DistrictName LIKE :search OR
        se.SectorName LIKE :search OR
        c.CellName LIKE :search OR
        v.VillageName LIKE :search
    )";
}

/* MAIN QUERY */
$sql = "
SELECT r.*, 
       s.Name as SupplierName,
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

$stmt = $conn->prepare($sql);

/* BIND DATE */
if (!empty($from) && !empty($to)) {
    $stmt->bindValue(':from', $from);
    $stmt->bindValue(':to', $to);
}

/* BIND SEARCH */
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();

$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalRecords = count($all);

/* PAGINATION */
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;

$paginated = array_slice($all, $start, $length);

$data = [];
$totalQty = 0;
$totalAmount = 0;

foreach ($paginated as $row) {

    $qty = $row['QuantityKg'];
    $price = $row['price'];
    $amount = $qty * $price;

    $totalQty += $qty;
    $totalAmount += $amount;

    $data[] = [
        "supplier" => $row['SupplierName'],
        "phone" => $row['phone'],
        "district" => $row['DistrictName'],
        "sector" => $row['SectorName'],
        "cell" => $row['CellName'],
        "village" => $row['VillageName'],
        "qty" => $qty,
        "price" => $price,
        "amount" => $amount,
        "date" => $row['DateReceived']
    ];
}

echo json_encode([
    "draw" => intval($_POST['draw']),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data,

    "totalQty" => $totalQty,
    "totalAmount" => number_format($totalAmount, 2)
]);
exit;