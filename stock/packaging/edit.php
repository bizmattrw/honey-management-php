<?php
ob_start();
include("../../config/db.php");
include("../../includes/layout.php");

$id = $_GET['id'];

// GET RECORD
$stmt = $conn->prepare("SELECT * FROM packaging WHERE PackagingID=?");
$stmt->execute([$id]);
$data = $stmt->fetch();

// PRODUCTS
$products = $conn->query("SELECT * FROM products")->fetchAll();

// STOCK
$stock = $conn->query("SELECT QuantityAvailableKg FROM processedhoneystock LIMIT 1")->fetch();
$availableProcessed = $stock['QuantityAvailableKg'];

if ($_POST) {

    $newProduct = $_POST['ProductID'];
    $newUnits = $_POST['QuantityProduced'];
    $newProcessed = $_POST['ProcessedUsedKg'];

    // OLD VALUES
    $oldProduct = $data['ProductID'];
    $oldUnits = $data['QuantityProduced'];
    $oldProcessed = $data['ProcessedUsedKg'];

    // 👉 STEP 1: RESTORE OLD STOCK
    $conn->prepare("
        UPDATE processedhoneystock 
        SET QuantityAvailableKg = QuantityAvailableKg + ?
    ")->execute([$oldProcessed]);

    $conn->prepare("
        UPDATE inventory 
        SET QuantityAvailable = QuantityAvailable - ?
        WHERE ProductID=?
    ")->execute([$oldUnits, $oldProduct]);

    // 👉 REFRESH STOCK
    $stock = $conn->query("SELECT QuantityAvailableKg FROM processedhoneystock LIMIT 1")->fetch();
    $availableProcessed = $stock['QuantityAvailableKg'];

    // VALIDATION
    if ($newProcessed > $availableProcessed) {

        echo "<div class='alert alert-danger'>❌ Not enough stock!</div>";

        // REVERT BACK (IMPORTANT)
        $conn->prepare("
            UPDATE processedhoneystock 
            SET QuantityAvailableKg = QuantityAvailableKg - ?
        ")->execute([$oldProcessed]);

        $conn->prepare("
            UPDATE inventory 
            SET QuantityAvailable = QuantityAvailable + ?
            WHERE ProductID=?
        ")->execute([$oldUnits, $oldProduct]);

    } else {

        // 👉 STEP 2: APPLY NEW VALUES

        // UPDATE RECORD
        $stmt = $conn->prepare("
            UPDATE packaging 
            SET ProductID=?, QuantityProduced=?, ProcessedUsedKg=?, PackagingDate=?
            WHERE PackagingID=?
        ");

        $stmt->execute([
            $newProduct,
            $newUnits,
            $newProcessed,
            $_POST['PackagingDate'],
            $id
        ]);

        // UPDATE PROCESSED STOCK
        $conn->prepare("
            UPDATE processedhoneystock 
            SET QuantityAvailableKg = QuantityAvailableKg - ?
        ")->execute([$newProcessed]);

        // UPDATE INVENTORY
        $conn->prepare("
            INSERT INTO inventory (ProductID, QuantityAvailable)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE 
            QuantityAvailable = QuantityAvailable + VALUES(QuantityAvailable)
        ")->execute([$newProduct, $newUnits]); 
    }
    header("Location: index.php");
}
?>

<div class="container mt-4">

<div class="card shadow">
<div class="card-header bg-warning">
    <h4>✏️ Edit Packaging</h4>
</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">
<label>Product</label>
<select id="productSelect" name="ProductID" class="form-select">
<?php foreach ($products as $p): ?>
<option value="<?= $p['ProductID'] ?>" 
    data-size="<?= $p['Size'] ?>"
    <?= $p['ProductID'] == $data['ProductID'] ? 'selected' : '' ?>>
    <?= $p['Name'] ?> (<?= $p['Size'] ?>)
</option>
<?php endforeach; ?>
</select>
</div>

<div class="mb-3">
<label>Units</label>
<input type="number" id="units" name="QuantityProduced" 
value="<?= $data['QuantityProduced'] ?>" class="form-control">
</div>

<div class="mb-3">
<label>Processed Used (Kg)</label>
<input type="number" id="processedKg" name="ProcessedUsedKg" 
value="<?= $data['ProcessedUsedKg'] ?>" class="form-control" readonly>
</div>

<div class="mb-3">
<label>Date</label>
<input type="date" name="PackagingDate" 
value="<?= $data['PackagingDate'] ?>" class="form-control">
</div>

<div class="alert alert-info">
Available Processed Stock: <?= $availableProcessed ?> Kg
<div id="preview" class="fw-bold mt-2"></div>
</div>

<button id="submitBtn" class="btn btn-success">Update</button>

</form>

</div>
</div>
</div>

<script>
const availableProcessed = <?= $availableProcessed ?>;

function convertToKg(size) {
    size = size.toLowerCase();
    if (size.includes("g")) return parseFloat(size)/1000;
    if (size.includes("kg")) return parseFloat(size);
    return 1;
}

function calculate() {

    let product = document.getElementById("productSelect");
    let units = document.getElementById("units").value;

    let size = product.options[product.selectedIndex].getAttribute("data-size");

    let kg = units * convertToKg(size);

    document.getElementById("processedKg").value = kg.toFixed(2);

    let remaining = availableProcessed - kg;

    document.getElementById("preview").innerHTML = `
    Remaining: <span class="${remaining < 0 ? 'text-danger':'text-success'}">
    ${remaining.toFixed(2)} Kg</span>`;

    document.getElementById("submitBtn").disabled = (remaining < 0);
}

document.getElementById("productSelect").addEventListener("change", calculate);
document.getElementById("units").addEventListener("input", calculate);
</script>

<?php include("../../includes/footer.php"); ?>