<?php 
ob_start();
include("../../includes/layout.php"); 
include("../../config/db.php");

/* =========================
   HELPER: CONVERT SIZE → KG
========================= */
function convertToKg($size){

    $size = strtolower(trim($size));
    preg_match('/[\d.]+/', $size, $matches);
    $value = floatval($matches[0] ?? 0);

    if(strpos($size, 'kg') !== false){
        return $value;
    }

    if(strpos($size, 'g') !== false){
        return $value / 1000;
    }

    if(strpos($size, 'ml') !== false){
        return ($value * 1.4) / 1000;
    }

    if(strpos($size, 'l') !== false){
        return $value * 1.4;
    }

    return $value;
}

/* FETCH PROCESSED STOCK */
$batches = $conn->query("
SELECT BatchNo, QuantityAvailableKg 
FROM processedhoneystock 
WHERE QuantityAvailableKg > 0
")->fetchAll(PDO::FETCH_ASSOC);

/* FETCH PRODUCTS */
$products = $conn->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

<div class="card shadow-lg">
<div class="card-header bg-primary text-white">
    <h5>📦 Packaging (Batch Based)</h5>
</div>

<div class="card-body">

<form method="POST" id="form">

<div class="row mb-3">

<div class="col-md-6">
<label>Batch No</label>
<select name="batch" id="batch" class="form-control" required>
<option value="">Select Batch</option>
<?php foreach($batches as $b): ?>
<option value="<?= $b['BatchNo'] ?>" data-qty="<?= $b['QuantityAvailableKg'] ?>">
<?= $b['BatchNo'] ?> (<?= $b['QuantityAvailableKg'] ?> Kg)
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-6">
<label>Available Processed Stock (Kg)</label>
<input type="text" id="available" class="form-control" readonly>
</div>

</div>

<table class="table table-bordered" id="productTable">
<thead class="table-dark">
<tr>
    <th>Product</th>
    <th>Size</th>
    <th>Quantity</th>
    <th>Used (Kg)</th>
    <th>Action</th>
</tr>
</thead>

<tbody id="rows"></tbody>
</table>

<button type="button" class="btn btn-success mb-3" onclick="addRow()">➕ Add Product</button>

<div class="mb-3">
<label>Total Processed Used (Kg)</label>
<input type="text" id="totalUsed" class="form-control" readonly>
</div>

<div class="mb-3">
<label>Packaging Date</label>
<input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control">
</div>

<div id="alertBox"></div>

<button class="btn btn-primary w-100">📦 Save Packaging</button>

</form>

</div>
</div>
</div>

<script>
let products = <?= json_encode($products) ?>;

/* SHOW AVAILABLE */
document.getElementById('batch').addEventListener('change', function(){
    let selected = this.options[this.selectedIndex];
    document.getElementById('available').value = selected.getAttribute('data-qty') || 0;
});

/* CONVERT SIZE → KG */
function convertToKg(size){

    size = size.toLowerCase().trim();
    let value = parseFloat(size) || 0;

    if(size.includes('kg')) return value;
    if(size.includes('g')) return value / 1000;
    if(size.includes('ml')) return (value * 1.4) / 1000;
    if(size.includes('l')) return value * 1.4;

    return value;
}

/* ADD ROW */
function addRow(){

let row = `
<tr>
<td>
<select name="product[]" class="form-control product" onchange="updateSize(this)">
<option value="">Select</option>
${products.map(p => `<option value="${p.ProductID}" data-size="${p.Size}">${p.Name} (${p.Size})</option>`).join('')}
</select>
</td>

<td><input type="text" class="form-control size" readonly></td>
<td><input type="number" name="qty[]" class="form-control qty" oninput="calculate()"></td>
<td><input type="text" class="form-control used" readonly></td>

<td><button type="button" class="btn btn-danger" onclick="this.closest('tr').remove(); calculate()">X</button></td>
</tr>
`;

document.getElementById('rows').insertAdjacentHTML('beforeend', row);
}

/* UPDATE SIZE */
function updateSize(select){
let size = select.options[select.selectedIndex].getAttribute('data-size') || '';
select.closest('tr').querySelector('.size').value = size;
calculate();
}

/* CALCULATE */
function calculate(){

let total = 0;

document.querySelectorAll('#rows tr').forEach(row => {

let sizeRaw = row.querySelector('.size').value;
let sizeKg = convertToKg(sizeRaw);

let qty = parseFloat(row.querySelector('.qty').value) || 0;

let used = sizeKg * qty;

row.querySelector('.used').value = used.toFixed(2);

total += used;
});

document.getElementById('totalUsed').value = total.toFixed(2);

/* VALIDATION */
let available = parseFloat(document.getElementById('available').value) || 0;

if(total > available){
    document.getElementById('alertBox').innerHTML = "<div class='alert alert-danger'>❌ Exceeds available processed stock</div>";
}else{
    document.getElementById('alertBox').innerHTML = "";
}
}

/* SUBMIT VALIDATION */
document.getElementById('form').addEventListener('submit', function(e){

let total = parseFloat(document.getElementById('totalUsed').value) || 0;
let available = parseFloat(document.getElementById('available').value) || 0;

if(total > available){
    e.preventDefault();
    alert("Not enough processed stock!");
}
});
</script>

<?php
/* ================= BACKEND ================= */
if($_SERVER['REQUEST_METHOD'] == "POST"){

$batch = $_POST['batch'];
$date  = $_POST['date'];
$products = $_POST['product'];
$qtys = $_POST['qty'];

/* CHECK STOCK */
$stmt = $conn->prepare("SELECT QuantityAvailableKg FROM processedhoneystock WHERE BatchNo=?");
$stmt->execute([$batch]);
$available = $stmt->fetchColumn();

$totalUsed = 0;

try{

$conn->beginTransaction();

for($i=0; $i<count($products); $i++){

$productID = $products[$i];
$qty = $qtys[$i];

/* GET SIZE */
$stmt = $conn->prepare("SELECT Size FROM products WHERE ProductID=?");
$stmt->execute([$productID]);
$sizeRaw = $stmt->fetchColumn();

$sizeKg = convertToKg($sizeRaw);
$used = $sizeKg * $qty;

$totalUsed += $used;

/* INSERT PACKAGING */
$conn->prepare("
INSERT INTO packaging (BatchID, ProductID, QuantityProduced, ProcessedUsedKg, PackagingDate,BatchNo)
VALUES (?, ?, ?, ?, ?, ?)
")->execute([$batch, $productID, $qty, $used, $date, $batch]);

/* UPDATE INVENTORY */
$stmt = $conn->prepare("SELECT InventoryID FROM inventory WHERE BatchNo=? AND ProductID=?");
$stmt->execute([$batch,$productID]);

if($stmt->rowCount() > 0){

$conn->prepare("
UPDATE inventory 
SET QuantityAvailable = QuantityAvailable + ?
WHERE BatchNo=? AND ProductID=?
")->execute([$qty,$batch,$productID]);

}else{

$conn->prepare("
INSERT INTO inventory (BatchNo, ProductID, QuantityAvailable)
VALUES (?, ?, ?)
")->execute([$batch,$productID,$qty]);

}

}

/* FINAL CHECK */
if($totalUsed > $available){
throw new Exception("Stock exceeded");
}

/* DEDUCT STOCK */
$conn->prepare("
UPDATE processedhoneystock 
SET QuantityAvailableKg = QuantityAvailableKg - ?
WHERE BatchNo=?
")->execute([$totalUsed,$batch]);

$conn->commit();

header("Location: index.php");

}catch(Exception $e){
$conn->rollBack();
echo "<div class='alert alert-danger'>❌ ".$e->getMessage()."</div>";
}

}
?>

<?php include("../../includes/footer.php"); ?>