<?php
ob_start();

include("../includes/layout.php");
include("../config/db.php");

/* ================= SIZE TO KG ================= */

function convertToKg($size){

    $size = strtolower(trim($size));

    preg_match('/[\d.]+/', $size, $m);

    $v = floatval($m[0] ?? 0);

    if(strpos($size,'kg') !== false) return $v;
    if(strpos($size,'g') !== false) return $v / 1000;
    if(strpos($size,'ml') !== false) return ($v * 1.4) / 1000;
    if(strpos($size,'l') !== false) return $v * 1.4;

    return $v;
}

/* ================= CUSTOMERS ================= */

$customers = $conn->query("
SELECT CustomerID, Name
FROM customers
ORDER BY Name ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* ================= INVENTORY ================= */

$inventory = $conn->query("
SELECT 
    i.*,
    p.Name,
    p.Size
FROM inventory i
JOIN products p 
ON i.ProductID = p.ProductID
WHERE i.QuantityAvailable > 0
")->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid mt-4">

<div class="card shadow border-0">

<div class="card-header bg-success text-white">
    <h4 class="mb-0">🛒 Sales Management</h4>
</div>

<div class="card-body">

<form method="POST" id="saleForm">

<div class="row">

<!-- CUSTOMER -->
<div class="col-md-4 mb-3">
<label class="form-label">Customer</label>

<select name="customer" class="form-control" required>
<option value="">Select Customer</option>

<?php foreach($customers as $c): ?>

<option value="<?= $c['CustomerID'] ?>">
<?= htmlspecialchars($c['Name']) ?>
</option>

<?php endforeach; ?>
</select>
</div>

<!-- SALE DATE -->
<div class="col-md-4 mb-3">
<label class="form-label">Sale Date</label>

<input 
type="date" 
name="sale_date"
class="form-control"
required
value="<?= date('Y-m-d') ?>">
</div>

<!-- PAYMENT -->
<div class="col-md-4 mb-3">
<label class="form-label">Amount Paid</label>

<input 
type="number"
step="0.01"
name="paid"
id="paid"
class="form-control"
oninput="calcBalance()">
</div>

</div>

<!-- TABLE -->
<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead class="table-dark">
<tr>
<th>Product</th>
<th>Batch</th>
<th>Available</th>
<th>Qty</th>
<th>Size</th>
<th>Used KG</th>
<th>Unit Price</th>
<th>Total</th>
<th>Action</th>
</tr>
</thead>

<tbody id="rows"></tbody>

</table>

</div>

<button 
type="button"
class="btn btn-primary mb-3"
onclick="addRow()">
➕ Add Item
</button>

<hr>

<div class="row">

<div class="col-md-6">
<label class="form-label">Grand Total</label>

<input 
type="text"
id="grandTotal"
class="form-control"
readonly>
</div>

<div class="col-md-6">
<label class="form-label">Balance</label>

<input 
type="text"
id="balance"
class="form-control"
readonly>
</div>

</div>

<br>

<button class="btn btn-success w-100">
💾 Save Sale
</button>

</form>

</div>
</div>
</div>

<script>

let inventory = <?= json_encode($inventory) ?>;

/* ================= ADD ROW ================= */

function addRow(){

let row = `
<tr>

<td>
<select 
name="product[]" 
class="form-control"
onchange="loadBatch(this)"
required>

<option value="">Select Product</option>

${[...new Set(inventory.map(i => i.ProductID))].map(pid => {

let p = inventory.find(i => i.ProductID == pid);

return `
<option value="${pid}">
${p.Name} (${p.Size})
</option>
`;

}).join('')}

</select>
</td>

<td>
<select 
name="batch[]" 
class="form-control"
onchange="updateRow(this)"
required>

<option value="">Select Batch</option>

</select>
</td>

<td>
<input class="form-control available" readonly>
</td>

<td>
<input 
type="number"
name="qty[]" 
class="form-control qty"
min="1"
oninput="calculate()"
required>

<small class="text-danger validation-msg"></small>
</td>

<td>
<input class="form-control size" readonly>
</td>

<td>
<input class="form-control usedkg" readonly>
</td>

<td>
<input 
type="number"
step="0.01"
name="price[]" 
class="form-control price"
oninput="calculate()"
required>
</td>

<td>
<input class="form-control total" readonly>
</td>

<td>
<button 
type="button"
class="btn btn-danger"
onclick="removeRow(this)">
X
</button>
</td>

</tr>
`;

document.getElementById('rows')
.insertAdjacentHTML('beforeend', row);

}

/* ================= REMOVE ROW ================= */

function removeRow(btn){

btn.closest('tr').remove();

calculate();

}

/* ================= LOAD BATCH ================= */

function loadBatch(sel){

let pid = sel.value;

let batch = sel.closest('tr')
.querySelector('[name="batch[]"]');

batch.innerHTML = `
<option value="">Select Batch</option>
`;

inventory
.filter(i => i.ProductID == pid)
.forEach(i => {

batch.innerHTML += `
<option 
value="${i.BatchNo}"
data-qty="${i.QuantityAvailable}"
data-size="${i.Size}">
${i.BatchNo}
</option>
`;

});

}

/* ================= UPDATE ROW ================= */

function updateRow(sel){

let opt = sel.options[sel.selectedIndex];

let row = sel.closest('tr');

/* AVAILABLE ITEMS */
row.querySelector('.available').dataset.value =
opt.dataset.qty;

row.querySelector('.available').value =
opt.dataset.qty + " Items";

/* PRODUCT SIZE */
row.querySelector('.size').value =
opt.dataset.size;

calculate();

}

/* ================= CONVERT SIZE ================= */

function convert(size){

size = size.toLowerCase();

let match = size.match(/[\d.]+/);

let v = parseFloat(match ? match[0] : 0);

if(size.includes('kg')) return v;
if(size.includes('g')) return v / 1000;
if(size.includes('ml')) return (v * 1.4) / 1000;
if(size.includes('l')) return v * 1.4;

return v;

}

/* ================= CALCULATE ================= */

function calculate(){

let grandTotal = 0;

document.querySelectorAll('#rows tr')
.forEach(r => {

let qty = parseFloat(
r.querySelector('.qty').value
) || 0;

let price = parseFloat(
r.querySelector('.price').value
) || 0;

/* USED KG ONLY FOR DISPLAY */
let sizeKg = convert(
r.querySelector('.size').value
);

let usedKg = qty * sizeKg;

r.querySelector('.usedkg').value =
usedKg.toFixed(2);

/* LINE TOTAL */
let total = qty * price;

r.querySelector('.total').value =
total.toFixed(2);

grandTotal += total;

/* VALIDATION */

let available = parseFloat(
r.querySelector('.available').dataset.value
) || 0;

let qtyInput = r.querySelector('.qty');
let msg = r.querySelector('.validation-msg');

if(qty > available){

qtyInput.style.border = "2px solid red";

msg.innerHTML = `
Entered quantity exceeds available stock!
Available: ${available} items
`;

}else{

qtyInput.style.border = "";
msg.innerHTML = "";

}

});

document.getElementById('grandTotal').value =
grandTotal.toFixed(2);

calcBalance();

}

/* ================= BALANCE ================= */

function calcBalance(){

let total = parseFloat(
document.getElementById('grandTotal').value
) || 0;

let paid = parseFloat(
document.getElementById('paid').value
) || 0;

document.getElementById('balance').value =
(total - paid).toFixed(2);

}

</script>
<script>
    document.getElementById('saleForm')
.addEventListener('submit', function(e){

let hasError = false;

document.querySelectorAll('#rows tr')
.forEach(r => {

let qty = parseFloat(
r.querySelector('.qty').value
) || 0;

let available = parseFloat(
r.querySelector('.available').dataset.value
) || 0;

if(qty > available){
hasError = true;
}

});

if(hasError){

e.preventDefault();

alert(
'Please fix quantities that exceed available stock.'
);

}

});
</script>
<?php

/* ================= SAVE SALE ================= */

if($_SERVER['REQUEST_METHOD'] == "POST"){

try{

$customer = $_POST['customer'];
$saleDate = $_POST['sale_date'];
$paid = $_POST['paid'] ?? 0;

$products = $_POST['product'] ?? [];
$batches = $_POST['batch'] ?? [];
$qtys = $_POST['qty'] ?? [];
$prices = $_POST['price'] ?? [];

$conn->beginTransaction();

$total = 0;

/* CREATE SALE */

$conn->prepare("
INSERT INTO sales
(CustomerID, SaleDate, TotalAmount, PaymentStatus)
VALUES(?,?,0,'Pending')
")->execute([

$customer,
$saleDate

]);

$saleID = $conn->lastInsertId();

/* LOOP ITEMS */

for($i = 0; $i < count($qtys); $i++){

$product = $products[$i];
$batch = $batches[$i];

$qty = floatval($qtys[$i]);
$price = floatval($prices[$i]);

/* AVAILABLE STOCK */

$stmt = $conn->prepare("
SELECT QuantityAvailable
FROM inventory
WHERE ProductID=? 
AND BatchNo=?
");

$stmt->execute([$product, $batch]);

$available = floatval(
$stmt->fetchColumn()
);

/* VALIDATE */

if($qty > $available){

throw new Exception(
"Stock exceeded for batch: $batch"
);

}

/* INSERT DETAILS */

$conn->prepare("
INSERT INTO saledetails
(SaleID, ProductID, BatchNo, Quantity, UnitPrice, TotalPrice)
VALUES(?,?,?,?,?,?)
")->execute([

$saleID,
$product,
$batch,
$qty,
$price,
($qty * $price)

]);

/* UPDATE STOCK */

$conn->prepare("
UPDATE inventory
SET QuantityAvailable =
QuantityAvailable - ?
WHERE ProductID=? 
AND BatchNo=?
")->execute([

$qty,
$product,
$batch

]);

$total += ($qty * $price);

}

/* UPDATE SALE */

$conn->prepare("
UPDATE sales
SET TotalAmount=?
WHERE SaleID=?
")->execute([

$total,
$saleID

]);

/* PAYMENT */

if($paid > 0){

$conn->prepare("
INSERT INTO payments
(SaleID, AmountPaid, PaymentDate, PaymentMethod)
VALUES(?, ?, ?, 'Cash')
")->execute([

$saleID,
$paid,
$saleDate

]);

}

/* PAYMENT STATUS */

$status = ($paid >= $total)
? 'Paid'
: (($paid > 0) ? 'Partial' : 'Pending');

$conn->prepare("
UPDATE sales
SET PaymentStatus=?
WHERE SaleID=?
")->execute([

$status,
$saleID

]);

$conn->commit();

echo "
<script>
alert('✅ Sale saved successfully!');
window.location='index.php';
</script>
";

}catch(Exception $e){

$conn->rollBack();

echo "
<div class='container mt-3'>
<div class='alert alert-danger'>
❌ ".$e->getMessage()."
</div>
</div>
";

}

}

include("../includes/footer.php");
?>