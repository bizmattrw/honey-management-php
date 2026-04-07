<?php
ob_start();
include("../includes/layout.php");
include("../config/db.php");

/* SIZE → KG */
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

/* DATA */
$customers = $conn->query("SELECT CustomerID, Name FROM customers")->fetchAll(PDO::FETCH_ASSOC);

$inventory = $conn->query("
SELECT i.*, p.Name, p.Size
FROM inventory i
JOIN products p ON i.ProductID = p.ProductID
WHERE i.QuantityAvailable > 0
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

<div class="card shadow-lg">
<div class="card-header bg-success text-white">
    <h5>🛒 Sales (Batch + Payment)</h5>
</div>

<div class="card-body">

<form method="POST" id="form">

<!-- CUSTOMER -->
<div class="mb-3">
<label>Customer</label>
<select name="customer" class="form-control" required>
<option value="">Select</option>
<?php foreach($customers as $c): ?>
<option value="<?= $c['CustomerID'] ?>"><?= $c['Name'] ?></option>
<?php endforeach; ?>
</select>
</div>

<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>Product</th>
<th>Batch</th>
<th>Available KG</th>
<th>Qty</th>
<th>Size</th>
<th>Used KG</th>
<th>Price</th>
<th>Total</th>
<th></th>
</tr>
</thead>

<tbody id="rows"></tbody>
</table>

<button type="button" class="btn btn-primary mb-3" onclick="addRow()">➕ Add Item</button>

<hr>

<div class="row">
<div class="col-md-4">
<label>Total</label>
<input type="text" id="total" class="form-control" readonly>
</div>

<div class="col-md-4">
<label>Amount Paid</label>
<input type="number" name="paid" id="paid" class="form-control" oninput="calcBalance()">
</div>

<div class="col-md-4">
<label>Balance</label>
<input type="text" id="balance" class="form-control" readonly>
</div>
</div>

<br>

<button class="btn btn-success w-100">💰 Save Sale</button>

</form>
</div>
</div>
</div>

<script>
let inventory = <?= json_encode($inventory) ?>;

function addRow(){

let row = `
<tr>

<td>
<select name="product[]" class="form-control" onchange="loadBatch(this)">
<option value="">Select</option>
${[...new Set(inventory.map(i=>i.ProductID))].map(pid=>{
let p = inventory.find(i=>i.ProductID==pid);
return `<option value="${pid}">${p.Name}</option>`;
}).join('')}
</select>
</td>

<td>
<select name="batch[]" class="form-control" onchange="updateRow(this)"></select>
</td>

<td><input class="form-control available" readonly></td>

<td><input name="qty[]" class="form-control qty" oninput="calculate()"></td>

<td><input class="form-control size" readonly></td>

<td><input class="form-control usedkg" readonly></td>

<td><input name="price[]" class="form-control price" oninput="calculate()"></td>

<td><input class="form-control total" readonly></td>

<td><button type="button" onclick="this.closest('tr').remove(); calculate()" class="btn btn-danger">X</button></td>

</tr>
`;

document.getElementById('rows').insertAdjacentHTML('beforeend', row);
}

function loadBatch(sel){
let pid = sel.value;
let batch = sel.closest('tr').querySelector('[name="batch[]"]');

batch.innerHTML = '<option value="">Select</option>';

inventory.filter(i=>i.ProductID==pid).forEach(i=>{
batch.innerHTML += `<option value="${i.BatchNo}" data-qty="${i.QuantityAvailable}" data-size="${i.Size}">
${i.BatchNo}
</option>`;
});
}

function updateRow(sel){
let opt = sel.options[sel.selectedIndex];
let row = sel.closest('tr');

row.querySelector('.available').value = opt.dataset.qty;
row.querySelector('.size').value = opt.dataset.size;

calculate();
}

function convert(size){
size = size.toLowerCase();
let v = parseFloat(size)||0;

if(size.includes('kg')) return v;
if(size.includes('g')) return v/1000;
if(size.includes('ml')) return (v*1.4)/1000;
if(size.includes('l')) return v*1.4;

return v;
}

function calculate(){

let total = 0;

document.querySelectorAll('#rows tr').forEach(r=>{

let qty = parseFloat(r.querySelector('.qty').value)||0;
let price = parseFloat(r.querySelector('.price').value)||0;

let kg = convert(r.querySelector('.size').value);
let used = qty * kg;

r.querySelector('.usedkg').value = used.toFixed(2);

let line = qty * price;
r.querySelector('.total').value = line.toFixed(2);

total += line;

/* VALIDATION */
let avail = parseFloat(r.querySelector('.available').value)||0;

if(used > avail){
r.querySelector('.qty').style.border="2px solid red";
}else{
r.querySelector('.qty').style.border="";
}

});

document.getElementById('total').value = total.toFixed(2);
calcBalance();
}

function calcBalance(){
let total = parseFloat(document.getElementById('total').value)||0;
let paid = parseFloat(document.getElementById('paid').value)||0;

document.getElementById('balance').value = (total - paid).toFixed(2);
}
</script>

<?php
if($_SERVER['REQUEST_METHOD']=="POST"){

$customer = $_POST['customer'];
$paid = $_POST['paid'] ?? 0;

$qtys = $_POST['qty'] ?? [];
$prices = $_POST['price'] ?? [];
$products = $_POST['product'] ?? [];
$batches = $_POST['batch'] ?? [];

try{

$conn->beginTransaction();

$total = 0;

/* CREATE SALE FIRST */
$conn->prepare("
INSERT INTO sales(CustomerID, SaleDate, TotalAmount, PaymentStatus)
VALUES(?, NOW(), 0, 'Pending')
")->execute([$customer]);

$saleID = $conn->lastInsertId();

/* LOOP ITEMS */
for($i=0;$i<count($qtys);$i++){

$qty = $qtys[$i];
$price = $prices[$i];
$product = $products[$i];
$batch = $batches[$i];

/* GET SIZE */
$stmt = $conn->prepare("SELECT Size FROM products WHERE ProductID=?");
$stmt->execute([$product]);
$size = $stmt->fetchColumn();

/* CONVERT */
$kg = convertToKg($size);
$usedKg = $qty * $kg;

/* CHECK STOCK */
$stmt = $conn->prepare("
SELECT QuantityAvailable FROM inventory
WHERE ProductID=? AND BatchNo=?
");
$stmt->execute([$product,$batch]);
$avail = $stmt->fetchColumn();

if($usedKg > $avail){
throw new Exception("Stock exceeded for batch $batch");
}

/* INSERT DETAILS */
$conn->prepare("
INSERT INTO saledetails(SaleID, ProductID,BatchNo, Quantity, UnitPrice, TotalPrice)
VALUES(?,?,?,?,?,?)
")->execute([
$saleID,$product,$batches[$i],$qty,$price,$qty*$price
]);

/* DEDUCT STOCK */
$conn->prepare("
UPDATE inventory
SET QuantityAvailable = QuantityAvailable - ?
WHERE ProductID=? AND BatchNo=?
")->execute([$usedKg,$product,$batch]);

$total += $qty*$price;
}

/* UPDATE SALE TOTAL */
$conn->prepare("UPDATE sales SET TotalAmount=? WHERE SaleID=?")
->execute([$total,$saleID]);

/* INSERT PAYMENT */
if($paid > 0){
$conn->prepare("
INSERT INTO payments(SaleID, AmountPaid, PaymentDate, PaymentMethod)
VALUES(?, ?, NOW(), 'Cash')
")->execute([$saleID,$paid]);
}

/* UPDATE STATUS */
$status = ($paid >= $total) ? 'Paid' : ($paid > 0 ? 'Partial' : 'Pending');

$conn->prepare("UPDATE sales SET PaymentStatus=? WHERE SaleID=?")
->execute([$status,$saleID]);

$conn->commit();

header("Location:index.php");

}catch(Exception $e){
$conn->rollBack();
echo "<div class='alert alert-danger'>❌ ".$e->getMessage()."</div>";
}

}
include('../includes/footer.php')
?>