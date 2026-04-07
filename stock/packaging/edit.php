<?php 
ob_start();
include("../../includes/layout.php"); 
include("../../config/db.php");

$id = $_GET['id'];

/* FETCH RECORD */
$stmt = $conn->prepare("
SELECT p.*, pr.Name, pr.Size 
FROM packaging p
JOIN products pr ON p.ProductID = pr.ProductID
WHERE PackagingID=?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$data){
    echo "Not found"; exit;
}

function convertToKg($size){
    $size = strtolower(trim($size));
    preg_match('/[\d.]+/', $size, $m);
    $v = floatval($m[0] ?? 0);

    if(strpos($size,'kg')!==false) return $v;
    if(strpos($size,'g')!==false) return $v/1000;
    if(strpos($size,'ml')!==false) return ($v*1.4)/1000;
    if(strpos($size,'l')!==false) return $v*1.4;

    return $v;
}
?>

<div class="container mt-4">
<div class="card shadow-lg">
<div class="card-header bg-warning">
    <h5>Edit Packaging</h5>
</div>

<div class="card-body">

<form method="POST">

<label>Product</label>
<input class="form-control mb-2" value="<?= $data['Name'] ?> (<?= $data['Size'] ?>)" readonly>

<label>Quantity</label>
<input type="number" name="qty" value="<?= $data['QuantityProduced'] ?>" class="form-control mb-2">

<label>Date</label>
<input type="date" name="date" value="<?= $data['PackagingDate'] ?>" class="form-control mb-2">

<button class="btn btn-warning">Update</button>

</form>

</div>
</div>
</div>

<?php
if($_SERVER['REQUEST_METHOD']=="POST"){

$newQty = $_POST['qty'];
$date   = $_POST['date'];

$oldQty  = $data['QuantityProduced'];
$oldUsed = $data['ProcessedUsedKg'];

$sizeKg = convertToKg($data['Size']);
$newUsed = $sizeKg * $newQty;

$batch = $data['BatchNo'];
$product = $data['ProductID'];

try{

$conn->beginTransaction();

/* ================= RESTORE OLD ================= */

/* processed */
$conn->prepare("
UPDATE processedhoneystock
SET QuantityAvailableKg = QuantityAvailableKg + ?
WHERE BatchNo=?
")->execute([$oldUsed,$batch]);

/* inventory */
$conn->prepare("
UPDATE inventory
SET QuantityAvailable = QuantityAvailable - ?
WHERE BatchNo=? AND ProductID=?
")->execute([$oldQty,$batch,$product]);

/* ================= APPLY NEW ================= */

/* check stock */
$stmt = $conn->prepare("SELECT QuantityAvailableKg FROM processedhoneystock WHERE BatchNo=?");
$stmt->execute([$batch]);
$available = $stmt->fetchColumn();

if($newUsed > $available){
    throw new Exception("Not enough processed stock");
}

/* deduct processed */
$conn->prepare("
UPDATE processedhoneystock
SET QuantityAvailableKg = QuantityAvailableKg - ?
WHERE BatchNo=?
")->execute([$newUsed,$batch]);

/* add inventory */
$conn->prepare("
UPDATE inventory
SET QuantityAvailable = QuantityAvailable + ?
WHERE BatchNo=? AND ProductID=?
")->execute([$newQty,$batch,$product]);

/* update record */
$conn->prepare("
UPDATE packaging
SET QuantityProduced=?, ProcessedUsedKg=?, PackagingDate=?
WHERE PackagingID=?
")->execute([$newQty,$newUsed,$date,$id]);

$conn->commit();

header("Location: index.php");

}catch(Exception $e){
$conn->rollBack();
echo "<div class='alert alert-danger'>".$e->getMessage()."</div>";
}
}
?>

<?php include("../../includes/footer.php"); ?>