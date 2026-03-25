<?php
ob_start();
include("../../config/db.php");
include("../../includes/layout.php");

$id = $_GET['id'];

// FETCH RECORD
$stmt = $conn->prepare("SELECT * FROM rawhoney WHERE RawHoneyID=?");
$stmt->execute([$id]);
$data = $stmt->fetch();

// FETCH SUPPLIERS
$suppliers = $conn->query("SELECT * FROM suppliers")->fetchAll();

if ($_POST) {

    $newQty = $_POST['QuantityKg'];
    $price = $_POST['price'];
    $total = $newQty * $price;

    // GET OLD QUANTITY
    $stmt = $conn->prepare("SELECT QuantityKg FROM rawhoney WHERE RawHoneyID=?");
    $stmt->execute([$id]);
    $old = $stmt->fetch();

    $oldQty = $old['QuantityKg'];

    // UPDATE RECORD
    $stmt = $conn->prepare("
        UPDATE rawhoney 
        SET SupplierID=?, QuantityKg=?, price=?, total=?, DateReceived=?
        WHERE RawHoneyID=?
    ");

    $stmt->execute([
        $_POST['SupplierID'],
        $newQty,
        $price,
        $total,
        $_POST['DateReceived'],
        $id
    ]);

    // UPDATE STOCK DIFFERENCE
    $difference = $newQty - $oldQty;

    $conn->prepare("
        UPDATE rawhoneystock 
        SET QuantityAvailableKg = QuantityAvailableKg + ?
    ")->execute([$difference]);

    header("Location: index.php");
    exit();
}
?>

<div class="container mt-4">

<div class="card shadow-lg border-0 rounded-4">
    <div class="card-header bg-warning text-dark">
        <h4>✏️ Edit Raw Honey</h4>
    </div>

    <div class="card-body">

        <form method="POST">

            <!-- Supplier -->
            <div class="mb-3">
                <label class="fw-bold">Supplier</label>
                <select name="SupplierID" class="form-select" required>
                    <?php foreach($suppliers as $s): ?>
                    <option value="<?= $s['SupplierID'] ?>"
                        <?= $s['SupplierID'] == $data['SupplierID'] ? 'selected' : '' ?>>
                        <?= $s['Name'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Quantity -->
            <div class="mb-3">
                <label class="fw-bold">Quantity (Kg)</label>
                <input type="number" step="0.01" name="QuantityKg" id="qty"
                    class="form-control"
                    value="<?= $data['QuantityKg'] ?>" required>
            </div>

            <!-- Price -->
            <div class="mb-3">
                <label class="fw-bold">Price</label>
                <input type="number" step="0.01" name="price" id="price"
                    class="form-control"
                    value="<?= $data['price'] ?>" required>
            </div>

            <!-- Total -->
            <div class="mb-3">
                <label class="fw-bold">Total</label>
                <input type="text" id="total" class="form-control bg-light"
                    value="<?= $data['total'] ?>" readonly>
            </div>

            <!-- Date -->
            <div class="mb-3">
                <label class="fw-bold">Date</label>
                <input type="date" name="DateReceived"
                    value="<?= $data['DateReceived'] ?>"
                    class="form-control" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">⬅ Back</a>

                <button class="btn btn-warning px-4">Update</button>
            </div>

        </form>

    </div>
</div>

</div>

<script>
document.getElementById("qty").addEventListener("input", calc);
document.getElementById("price").addEventListener("input", calc);

function calc() {
    let q = parseFloat(document.getElementById("qty").value) || 0;
    let p = parseFloat(document.getElementById("price").value) || 0;

    document.getElementById("total").value = (q * p).toFixed(2);
}
</script>

<?php include("../../includes/footer.php"); ?>