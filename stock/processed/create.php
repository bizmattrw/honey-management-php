<?php
ob_start();
include("../../config/db.php");
include("../../includes/layout.php");

// GET RAW STOCK
$stock = $conn->query("SELECT QuantityAvailableKg FROM rawhoneystock LIMIT 1")->fetch();
$availableStock = $stock['QuantityAvailableKg'];

if ($_POST) {

    $input = $_POST['InputQuantityKg'];
    $output = $_POST['OutputQuantityKg'];

    $errors = [];

    // SERVER VALIDATION
    if ($input > $availableStock) {
        $errors[] = "Raw quantity exceeds available stock!";
    }

    if ($output > $input) {
        $errors[] = "Processed quantity cannot be greater than raw used!";
    }

    if ($input <= 0 || $output <= 0) {
        $errors[] = "Quantities must be greater than zero!";
    }

    if (count($errors) == 0) {

        // INSERT PROCESSING BATCH
        $stmt = $conn->prepare("
            INSERT INTO processingbatch
            (InputQuantityKg, OutputQuantityKg, ProcessingDate, Status)
            VALUES (?,?,?,?)
        ");

        $stmt->execute([
            $input,
            $output,
            $_POST['ProcessingDate'],
            $_POST['Status']
        ]);

        // UPDATE RAW STOCK
        $conn->prepare("
            UPDATE rawhoneystock 
            SET QuantityAvailableKg = QuantityAvailableKg - ?
        ")->execute([$input]);

        // UPDATE PROCESSED STOCK
        $conn->prepare("
            UPDATE processedhoneystock 
            SET QuantityAvailableKg = QuantityAvailableKg + ?
        ")->execute([$output]);

        echo "<div class='alert alert-success'>Processing recorded successfully!</div>";

    } else {
        foreach ($errors as $err) {
            echo "<div class='alert alert-danger'>$err</div>";
        }
    }
}
?>

<div class="container mt-4">

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">⚙️ Process Raw Honey</h4>
        </div>

        <div class="card-body">

            <form method="POST">

                <!-- Raw Input -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Raw Honey Used (Kg)</label>
                    <input type="number" step="0.01" name="InputQuantityKg" id="inputQty"
                        class="form-control" required>
                    <small id="rawError" class="text-danger"></small>
                </div>

                <!-- Processed Output -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Processed Honey Produced (Kg)</label>
                    <input type="number" step="0.01" name="OutputQuantityKg" id="outputQty"
                        class="form-control" required>
                    <small id="processedError" class="text-danger"></small>
                </div>

                <!-- Date -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Processing Date</label>
                    <input type="date" name="ProcessingDate" class="form-control" required>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="Status" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <!-- Available Stock Info -->
                <div class="alert alert-info">
                    Available Raw Stock: <strong><?= $availableStock ?> Kg</strong>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">⬅ Back</a>
                    <button type="submit" id="submitBtn" class="btn btn-success">💾 Save</button>
                </div>

            </form>

        </div>
    </div>

</div>

<!-- FRONTEND VALIDATION -->
<script>
const availableStock = <?= $availableStock ?>;

const inputQty = document.getElementById("inputQty");
const outputQty = document.getElementById("outputQty");

const rawError = document.getElementById("rawError");
const processedError = document.getElementById("processedError");
const submitBtn = document.getElementById("submitBtn");

function validate() {
    let input = parseFloat(inputQty.value) || 0;
    let output = parseFloat(outputQty.value) || 0;

    let valid = true;

    rawError.innerText = "";
    processedError.innerText = "";

    // Raw stock validation
    if (input > availableStock) {
        rawError.innerText = "❌ Exceeds available stock (" + availableStock + " Kg)";
        valid = false;
    }

    // Process validation
    if (output > input) {
        processedError.innerText = "❌ Cannot exceed raw input";
        valid = false;
    }

    if (input <= 0) {
        rawError.innerText = "❌ Must be greater than 0";
        valid = false;
    }

    if (output <= 0) {
        processedError.innerText = "❌ Must be greater than 0";
        valid = false;
    }

    submitBtn.disabled = !valid;
}

inputQty.addEventListener("input", validate);
outputQty.addEventListener("input", validate);
</script>

<?php include("../../includes/footer.php"); ?>