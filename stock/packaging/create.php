<?php
ob_start();
include("../../config/db.php");
include("../../includes/layout.php");

// FETCH PRODUCTS
$products = $conn->query("SELECT * FROM products")->fetchAll();

// FETCH PROCESSED STOCK
$stock = $conn->query("SELECT QuantityAvailableKg FROM processedhoneystock LIMIT 1")->fetch();
$availableProcessed = $stock['QuantityAvailableKg'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $productIds = $_POST['ProductID'] ?? [];
    $unitsList = $_POST['QuantityProduced'] ?? [];
    $processedList = $_POST['ProcessedUsedKg'] ?? [];
    $date = $_POST['PackagingDate'];

    // ✅ FORCE ARRAYS (fix error)
    if (!is_array($productIds)) $productIds = [$productIds];
    if (!is_array($unitsList)) $unitsList = [$unitsList];
    if (!is_array($processedList)) $processedList = [$processedList];

    // ✅ SAFE SUM
    $totalProcessed = array_sum(array_map('floatval', $processedList));

    if ($totalProcessed > $availableProcessed) {

        echo "<div class='alert alert-danger'>❌ Not enough processed stock!</div>";

    } else {

        for ($i = 0; $i < count($productIds); $i++) {

            $productId = $productIds[$i];
            $units = intval($unitsList[$i]);
            $processed = floatval($processedList[$i]);

            if ($units <= 0 || !$productId) continue;

            // INSERT PACKAGING
            $stmt = $conn->prepare("
                INSERT INTO packaging
                (ProductID, QuantityProduced, ProcessedUsedKg, PackagingDate)
                VALUES (?,?,?,?)
            ");
            $stmt->execute([$productId, $units, $processed, $date]);

            // UPDATE INVENTORY
            $conn->prepare("
                INSERT INTO inventory (ProductID, QuantityAvailable)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE 
                QuantityAvailable = QuantityAvailable + VALUES(QuantityAvailable)
            ")->execute([$productId, $units]);
        }

        // UPDATE PROCESSED STOCK ONCE
        $conn->prepare("
            UPDATE processedhoneystock 
            SET QuantityAvailableKg = QuantityAvailableKg - ?
        ")->execute([$totalProcessed]);

        
    }
    header("Location: index.php");
}
?>

<div class="container mt-4">

<div class="card shadow rounded-4">
<div class="card-header bg-dark text-white">
    <h4>📦 Product Packaging</h4>
</div>

<div class="card-body">

<form method="POST">

<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Product</th>
            <th>Units</th>
            <th>Processed (Kg)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="rows"></tbody>
</table>

<button type="button" class="btn btn-primary" onclick="addRow()">+ Add Product</button>

<div class="mt-3">
    <label>Date</label>
    <input type="date" name="PackagingDate" class="form-control" required>
</div>

<div class="alert alert-info mt-3">
    Available Processed Stock: <strong><?= $availableProcessed ?> Kg</strong>
    <div id="summary" class="mt-2 fw-bold"></div>
</div>

<button id="submitBtn" class="btn btn-success mt-3">💾 Save All</button>

</form>

</div>
</div>
</div>

<script>
const availableProcessed = <?= $availableProcessed ?>;

function convertToKg(size) {
    size = size.toLowerCase();
    if (size.includes("g")) return parseFloat(size) / 1000;
    if (size.includes("kg")) return parseFloat(size);
    return 1;
}

function addRow() {

    let row = `
    <tr>
        <td>
            <select name="ProductID[]" class="form-select product">
                <option value="">Select Product</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['ProductID'] ?>" data-size="<?= $p['Size'] ?>">
                        <?= $p['Name'] ?> (<?= $p['Size'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </td>

        <td>
            <input type="number" name="QuantityProduced[]" class="form-control units" min="1">
        </td>

        <td>
            <input type="number" name="ProcessedUsedKg[]" class="form-control kg bg-light" readonly>
        </td>

        <td>
            <button type="button" class="btn btn-danger" onclick="removeRow(this)">X</button>
        </td>
    </tr>
    `;

    document.getElementById("rows").insertAdjacentHTML("beforeend", row);
}

function removeRow(btn) {
    btn.closest("tr").remove();
    calculateAll();
}

function calculateAll() {

    let total = 0;

    document.querySelectorAll("#rows tr").forEach(row => {

        let product = row.querySelector(".product");
        let units = row.querySelector(".units");
        let kgInput = row.querySelector(".kg");

        let size = product.options[product.selectedIndex]?.getAttribute("data-size");

        if (!size) {
            kgInput.value = "";
            return;
        }

        let unitKg = convertToKg(size);
        let unitsVal = parseInt(units.value) || 0;

        let totalKg = unitsVal * unitKg;

        kgInput.value = totalKg.toFixed(2);

        total += totalKg;
    });

    let remaining = availableProcessed - total;

    document.getElementById("summary").innerHTML = `
        Total Used: <span class="text-danger">${total.toFixed(2)} Kg</span><br>
        Remaining: <span class="${remaining < 0 ? 'text-danger' : 'text-success'}">
            ${remaining.toFixed(2)} Kg
        </span>
    `;

    if (total > availableProcessed || total <= 0) {
        document.getElementById("submitBtn").disabled = true;
    } else {
        document.getElementById("submitBtn").disabled = false;
    }
}

// EVENTS
document.addEventListener("input", calculateAll);
document.addEventListener("change", calculateAll);

// INITIAL ROW
addRow();
</script>

<?php include("../../includes/footer.php"); ?>