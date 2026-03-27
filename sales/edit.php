<?php
ob_start();
include("../config/db.php");
include("../includes/layout.php");

$id = $_GET['id'];

// FETCH SALE
$sale = $conn->query("SELECT * FROM sales WHERE SaleID=$id")->fetch();

// FETCH CUSTOMERS & PRODUCTS
$customers = $conn->query("SELECT * FROM customers")->fetchAll();
$products = $conn->query("SELECT * FROM products")->fetchAll();

// FETCH SALE ITEMS
$items = $conn->query("
SELECT * FROM saledetails WHERE SaleID=$id
")->fetchAll();

// HANDLE UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer = $_POST['CustomerID'];
    $productIds = $_POST['ProductID'];
    $quantities = $_POST['Quantity'];
    $prices = $_POST['Price'];
    $totals = $_POST['Total'];

    // FORCE ARRAYS
    if (!is_array($productIds)) $productIds = [$productIds];
    if (!is_array($quantities)) $quantities = [$quantities];
    if (!is_array($prices)) $prices = [$prices];
    if (!is_array($totals)) $totals = [$totals];

    // 🔥 STEP 1: RESTORE OLD INVENTORY
    foreach ($items as $old) {
        $conn->prepare("
            UPDATE inventory 
            SET QuantityAvailable = QuantityAvailable + ?
            WHERE ProductID=?
        ")->execute([$old['Quantity'], $old['ProductID']]);
    }

    // 🔥 STEP 2: DELETE OLD DETAILS
    $conn->prepare("DELETE FROM saledetails WHERE SaleID=?")->execute([$id]);

    // 🔥 STEP 3: VALIDATE STOCK
    for ($i = 0; $i < count($productIds); $i++) {

        $check = $conn->prepare("SELECT QuantityAvailable FROM inventory WHERE ProductID=?");
        $check->execute([$productIds[$i]]);
        $stock = $check->fetch();

        if ($stock['QuantityAvailable'] < $quantities[$i]) {

            echo "<div class='alert alert-danger'>❌ Not enough stock!</div>";

            // ❗ REVERT BACK (IMPORTANT)
            foreach ($items as $old) {
                $conn->prepare("
                    UPDATE inventory 
                    SET QuantityAvailable = QuantityAvailable - ?
                    WHERE ProductID=?
                ")->execute([$old['Quantity'], $old['ProductID']]);
            }

            return;
        }
    }

    // 🔥 STEP 4: INSERT NEW DETAILS + REDUCE STOCK
    $grandTotal = 0;

    for ($i = 0; $i < count($productIds); $i++) {

        $conn->prepare("
            INSERT INTO saledetails (SaleID, ProductID, Quantity, UnitPrice, TotalPrice)
            VALUES (?,?,?,?,?)
        ")->execute([
            $id,
            $productIds[$i],
            $quantities[$i],
            $prices[$i],
            $totals[$i]
        ]);

        // REDUCE STOCK
        $conn->prepare("
            UPDATE inventory 
            SET QuantityAvailable = QuantityAvailable - ?
            WHERE ProductID=?
        ")->execute([$quantities[$i], $productIds[$i]]);

        $grandTotal += $totals[$i];
    }

    // 🔥 STEP 5: UPDATE SALE TOTAL
    $conn->prepare("
        UPDATE sales 
        SET CustomerID=?, TotalAmount=? 
        WHERE SaleID=?
    ")->execute([$customer, $grandTotal, $id]);

    // 🔥 STEP 6: UPDATE PAYMENT STATUS
    $paid = $conn->query("
        SELECT IFNULL(SUM(AmountPaid),0) as paid 
        FROM payments WHERE SaleID=$id
    ")->fetch()['paid'];

    if ($paid >= $grandTotal) {
        $status = "Paid";
    } elseif ($paid > 0) {
        $status = "Partial";
    } else {
        $status = "Pending";
    }

    $conn->query("UPDATE sales SET PaymentStatus='$status' WHERE SaleID=$id");

    echo "<div class='alert alert-success'>✅ Sale updated successfully!</div>";

    // REFRESH ITEMS
    $items = $conn->query("SELECT * FROM saledetails WHERE SaleID=$id")->fetchAll();
    header("Location: index.php");
}
?>

<div class="container mt-4">

<div class="card shadow">
<div class="card-header bg-warning">
    <h4>✏️ Edit Sale #<?= $id ?></h4>
</div>

<div class="card-body">

<form method="POST">

<!-- CUSTOMER -->
<div class="mb-3">
<label>Customer</label>
<select name="CustomerID" class="form-select">
<?php foreach($customers as $c): ?>
<option value="<?= $c['CustomerID'] ?>" 
<?= $c['CustomerID']==$sale['CustomerID']?'selected':'' ?>>
<?= $c['Name'] ?>
</option>
<?php endforeach; ?>
</select>
</div>

<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>Product</th>
<th>Price</th>
<th>Qty</th>
<th>Total</th>
<th>Action</th>
</tr>
</thead>

<tbody id="rows">

<?php foreach($items as $i): ?>
<tr>
<td>
<select name="ProductID[]" class="form-select product">
<?php foreach($products as $p): ?>
<option value="<?= $p['ProductID'] ?>"
data-price="<?= $p['Price'] ?>"
<?= $p['ProductID']==$i['ProductID']?'selected':'' ?>>
<?= $p['Name'] ?>
</option>
<?php endforeach; ?>
</select>
</td>

<td><input type="number" name="Price[]" value="<?= $i['UnitPrice'] ?>" class="form-control price"></td>
<td><input type="number" name="Quantity[]" value="<?= $i['Quantity'] ?>" class="form-control qty"></td>
<td><input type="number" name="Total[]" value="<?= $i['TotalPrice'] ?>" class="form-control total"></td>
<td><button type="button" class="btn btn-danger" onclick="removeRow(this)">X</button></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<button type="button" class="btn btn-primary" onclick="addRow()">+ Add Product</button>

<h5 class="mt-3">Grand Total: <span id="grandTotal">0</span></h5>

<button class="btn btn-success mt-3">💾 Update Sale</button>

</form>

</div>
</div>
</div>

<script>
const products = <?php echo json_encode($products); ?>;

function addRow(){
let options = `<option>Select</option>`;
products.forEach(p=>{
options += `<option value="${p.ProductID}" data-price="${p.Price}">${p.Name}</option>`;
});

let row = `
<tr>
<td><select name="ProductID[]" class="form-select product">${options}</select></td>
<td><input name="Price[]" class="form-control price"></td>
<td><input name="Quantity[]" class="form-control qty"></td>
<td><input name="Total[]" class="form-control total"></td>
<td><button type="button" onclick="removeRow(this)" class="btn btn-danger">X</button></td>
</tr>`;
document.getElementById("rows").insertAdjacentHTML("beforeend", row);
}

function removeRow(btn){
btn.closest("tr").remove();
calculate();
}

function calculate(){
let grand = 0;

document.querySelectorAll("#rows tr").forEach(row=>{
let product = row.querySelector(".product");
let price = row.querySelector(".price");
let qty = row.querySelector(".qty");
let total = row.querySelector(".total");

let p = product.options[product.selectedIndex]?.getAttribute("data-price") || 0;
price.value = p;

let t = p * (qty.value || 0);
total.value = t.toFixed(2);

grand += t;
});

document.getElementById("grandTotal").innerText = grand.toFixed(2);
}

document.addEventListener("input", calculate);
document.addEventListener("change", calculate);

calculate();
</script>

<?php include("../includes/footer.php"); ?>