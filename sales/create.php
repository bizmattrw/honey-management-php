<?php
ob_start();
include("../config/db.php");
include("../includes/layout.php");

// FETCH DATA
$products = $conn->query("SELECT * FROM products")->fetchAll();
$customers = $conn->query("SELECT * FROM customers")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer = $_POST['CustomerID'];
    $productIds = $_POST['ProductID'];
    $quantities = $_POST['Quantity'];
    $prices = $_POST['Price'];
    $totals = $_POST['Total'];

    $amountPaid = $_POST['AmountPaid'] ?? 0;
    $paymentMethod = $_POST['PaymentMethod'] ?? null;

    // FORCE ARRAYS
    if (!is_array($productIds)) $productIds = [$productIds];
    if (!is_array($quantities)) $quantities = [$quantities];
    if (!is_array($prices)) $prices = [$prices];
    if (!is_array($totals)) $totals = [$totals];

    $grandTotal = array_sum(array_map('floatval', $totals));

    // CHECK INVENTORY
    for ($i = 0; $i < count($productIds); $i++) {

        $check = $conn->prepare("SELECT QuantityAvailable FROM inventory WHERE ProductID=?");
        $check->execute([$productIds[$i]]);
        $stock = $check->fetch();

        if ($stock['QuantityAvailable'] < $quantities[$i]) {
            echo "<div class='alert alert-danger'>❌ Not enough stock for selected product!</div>";
            return;
        }
    }

    // DETERMINE PAYMENT STATUS
    if ($amountPaid == 0) {
        $status = "Pending";
    } elseif ($amountPaid < $grandTotal) {
        $status = "Partial";
    } else {
        $status = "Paid";
    }

    // INSERT INTO SALES
    $stmt = $conn->prepare("
        INSERT INTO sales (CustomerID, SaleDate, TotalAmount, PaymentStatus)
        VALUES (?, CURDATE(), ?, ?)
    ");
    $stmt->execute([$customer, $grandTotal, $status]);

    $saleId = $conn->lastInsertId();

    // INSERT SALE DETAILS + UPDATE INVENTORY
    for ($i = 0; $i < count($productIds); $i++) {

        $conn->prepare("
            INSERT INTO saledetails (SaleID, ProductID, Quantity, UnitPrice, TotalPrice)
            VALUES (?,?,?,?,?)
        ")->execute([
            $saleId,
            $productIds[$i],
            $quantities[$i],
            $prices[$i],
            $totals[$i]
        ]);

        // REDUCE INVENTORY
        $conn->prepare("
            UPDATE inventory 
            SET QuantityAvailable = QuantityAvailable - ?
            WHERE ProductID=?
        ")->execute([$quantities[$i], $productIds[$i]]);
    }

    // INSERT PAYMENT (IF ANY)
    if ($amountPaid > 0) {

        $conn->prepare("
            INSERT INTO payments (SaleID, AmountPaid, PaymentDate, PaymentMethod)
            VALUES (?, ?, CURDATE(), ?)
        ")->execute([$saleId, $amountPaid, $paymentMethod]);
    }

   header("Location: index.php");
}
?>

<div class="container mt-4">

<div class="card shadow">
<div class="card-header bg-success text-white">
    <h4>🛒 New Sale</h4>
</div>

<div class="card-body">

<form method="POST">

<!-- CUSTOMER -->
<div class="mb-3">
<label>Customer</label>
<select name="CustomerID" class="form-select" required>
<option value="">Select Customer</option>
<?php foreach ($customers as $c): ?>
<option value="<?= $c['CustomerID'] ?>">
    <?= $c['Name']  ?>
</option>
<?php endforeach; ?>
</select>
</div>

<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>Product</th>
<th>Price</th>
<th>Quantity</th>
<th>Total</th>
<th>Action</th>
</tr>
</thead>
<tbody id="rows"></tbody>
</table>

<button type="button" class="btn btn-primary" onclick="addRow()">+ Add Product</button>

<hr>

<!-- PAYMENT -->
<div class="row">
<div class="col-md-4">
<label>Amount Paid</label>
<input type="number" step="0.01" name="AmountPaid" class="form-control">
</div>

<div class="col-md-4">
<label>Payment Method</label>
<select name="PaymentMethod" class="form-select">
<option value="Cash">Cash</option>
<option value="Mobile Money">Mobile Money</option>
<option value="Bank">Bank</option>
<option value="Credit">Credit</option>
</select>
</div>
</div>

<div class="mt-3">
<h5>Grand Total: <span id="grandTotal">0</span></h5>
</div>

<button class="btn btn-success mt-3">💾 Save Sale</button>

</form>

</div>
</div>
</div>

<script>
const products = <?php echo json_encode($products); ?>;

function addRow() {

    let options = `<option value="">Select</option>`;
    products.forEach(p => {
        options += `<option value="${p.ProductID}" data-price="${p.Price}">
            ${p.Name} (${p.Size})
        </option>`;
    });

    let row = `
    <tr>
        <td><select name="ProductID[]" class="form-select product">${options}</select></td>
        <td><input type="number" name="Price[]" class="form-control price" readonly></td>
        <td><input type="number" name="Quantity[]" class="form-control qty"></td>
        <td><input type="number" name="Total[]" class="form-control total" readonly></td>
        <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">X</button></td>
    </tr>
    `;

    document.getElementById("rows").insertAdjacentHTML("beforeend", row);
}

function removeRow(btn) {
    btn.closest("tr").remove();
    calculateTotal();
}

function calculateTotal() {

    let grand = 0;

    document.querySelectorAll("#rows tr").forEach(row => {

        let product = row.querySelector(".product");
        let priceInput = row.querySelector(".price");
        let qtyInput = row.querySelector(".qty");
        let totalInput = row.querySelector(".total");

        let price = product.options[product.selectedIndex]?.getAttribute("data-price") || 0;
        priceInput.value = price;

        let qty = qtyInput.value || 0;
        let total = price * qty;

        totalInput.value = total.toFixed(2);

        grand += total;
    });

    document.getElementById("grandTotal").innerText = grand.toFixed(2);
}

document.addEventListener("change", calculateTotal);
document.addEventListener("input", calculateTotal);

// INIT
addRow();
</script>

<?php include("../includes/footer.php"); ?>