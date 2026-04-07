<?php
ob_start();
include("../../config/db.php");
include("../../includes/layout.php");

// FETCH SUPPLIERS
$suppliers = $conn->query("SELECT * FROM suppliers")->fetchAll();

if ($_POST) {
function generateBatchNo($rawID, $supplierID){
    $date = date("Ymd");
    return $rawID . "SM" . $date . $supplierID;
}
    $qty = $_POST['QuantityKg'];
    $price = $_POST['price'];
    $total = $qty * $price;

    $stmt = $conn->prepare("
        INSERT INTO rawhoney 
        (SupplierID, QuantityKg, price, total, DateReceived)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['SupplierID'],
        $qty,
        $price,
        $total,
        $_POST['DateReceived']
    ]);
$rawID = $conn->lastInsertId();

/* GENERATE BATCH */
$batchNo = generateBatchNo($rawID, $_POST['supplierID']);

/* UPDATE RECORD WITH BATCH */
$conn->prepare("UPDATE rawhoney SET BatchNo=? WHERE RawHoneyID=?")
     ->execute([$batchNo, $rawID]);
    // UPDATE STOCK
    // $conn->prepare("
    //     UPDATE rawhoneystock 
    //     SET QuantityAvailableKg = QuantityAvailableKg + ?
    // ")->execute([$qty]);
/* INSERT INTO STOCK */
$conn->prepare("
INSERT INTO rawhoneystock (BatchNo, QuantityAvailableKg)
VALUES (?, ?)
")->execute([$batchNo, $qty]);

    header("Location: index.php");
}
?>

<div class="container mt-4">

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">🍯 Add Raw Honey</h4>
        </div>

        <div class="card-body">

            <form method="POST">

                <!-- Supplier -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Supplier</label>
                    <select name="SupplierID" class="form-select" required>
                        <option value="">-- Select Supplier --</option>
                        <?php foreach($suppliers as $s): ?>
                        <option value="<?= $s['SupplierID'] ?>">
                            <?= $s['Name'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Quantity -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Quantity (Kg)</label>
                    <input type="number" step="0.01" name="QuantityKg" id="qty"
                        class="form-control" placeholder="Enter quantity" required>
                </div>

                <!-- Price -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Price per Kg</label>
                    <input type="number" step="0.01" name="price" id="price"
                        class="form-control" placeholder="Enter price" required>
                </div>

                <!-- Total -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Total</label>
                    <input type="text" id="total" class="form-control bg-light" readonly>
                </div>

                <!-- Date -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Date Received</label>
                    <input type="date" name="DateReceived" class="form-control" required>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">⬅ Back</a>

                    <button type="submit" class="btn btn-success px-4">
                        💾 Save Record
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<!-- AUTO CALCULATE TOTAL -->
<script>
document.getElementById("qty").addEventListener("input", calculateTotal);
document.getElementById("price").addEventListener("input", calculateTotal);

function calculateTotal() {
    let qty = parseFloat(document.getElementById("qty").value) || 0;
    let price = parseFloat(document.getElementById("price").value) || 0;

    document.getElementById("total").value = (qty * price).toFixed(2);
}
</script>

<?php include("../../includes/footer.php"); ?>