<?php 
ob_start();
include("../../includes/layout.php"); 
include("../../config/db.php"); 

/* FETCH AVAILABLE BATCHES */
$stmt = $conn->prepare("
SELECT BatchNo, QuantityAvailableKg 
FROM rawhoneystock 
WHERE QuantityAvailableKg > 0
");
$stmt->execute();
$batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

<div class="card shadow-lg">
<div class="card-header bg-success text-white">
    <h5>⚙️ Process Honey (Batch Based)</h5>
</div>

<div class="card-body">

<form method="POST" id="form">

<div class="row">

<!-- BATCH -->
<div class="col-md-6 mb-3">
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

<!-- AVAILABLE -->
<div class="col-md-6 mb-3">
<label>Available Raw Stock</label>
<input type="text" id="available" class="form-control" readonly>
</div>

<!-- INPUT -->
<div class="col-md-6 mb-3">
<label>Input Quantity (Kg)</label>
<input type="number" step="0.01" name="input" id="input" class="form-control" required>
</div>

<!-- OUTPUT -->
<div class="col-md-6 mb-3">
<label>Output Quantity (Kg)</label>
<input type="number" step="0.01" name="output" id="output" class="form-control" required>
</div>

<!-- DATE -->
<div class="col-md-6 mb-3">
<label>Date</label>
<input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
</div>

</div>

<div id="alertBox"></div>

<button class="btn btn-success w-100">Process</button>

</form>

</div>
</div>
</div>

<!-- ================= JS VALIDATION ================= -->
<script>
let batch = document.getElementById('batch');
let availableInput = document.getElementById('available');
let inputField = document.getElementById('input');
let outputField = document.getElementById('output');
let alertBox = document.getElementById('alertBox');

/* SHOW AVAILABLE */
batch.addEventListener('change', function(){
    let selected = this.options[this.selectedIndex];
    let qty = selected.getAttribute('data-qty') || 0;
    availableInput.value = qty;
});

/* LIVE VALIDATION */
function validateFields(){

    let available = parseFloat(availableInput.value) || 0;
    let input = parseFloat(inputField.value) || 0;
    let output = parseFloat(outputField.value) || 0;

    alertBox.innerHTML = "";

    if(input > available){
        alertBox.innerHTML = "<div class='alert alert-danger'>❌ Input exceeds available stock</div>";
        return false;
    }

    if(output > input){
        alertBox.innerHTML = "<div class='alert alert-warning'>⚠️ Output cannot exceed input</div>";
        return false;
    }

    return true;
}

/* TRIGGER VALIDATION ON INPUT */
inputField.addEventListener('input', validateFields);
outputField.addEventListener('input', validateFields);

/* FORM SUBMIT VALIDATION */
document.getElementById('form').addEventListener('submit', function(e){
    if(!validateFields()){
        e.preventDefault();
    }
});
</script>

<?php
/* ================= BACKEND ================= */
if($_SERVER['REQUEST_METHOD'] == "POST"){

$batch = $_POST['batch'];
$input = floatval($_POST['input']);
$output = floatval($_POST['output']);
$date = $_POST['date'];

/* CHECK STOCK */
$stmt = $conn->prepare("SELECT QuantityAvailableKg FROM rawhoneystock WHERE BatchNo=?");
$stmt->execute([$batch]);
$available = $stmt->fetchColumn();

if(!$available || $input > $available){
    echo "<div class='container'><div class='alert alert-danger'>❌ Not enough stock</div></div>";
    exit;
}

if($output > $input){
    echo "<div class='container'><div class='alert alert-warning'>⚠️ Invalid output</div></div>";
    exit;
}

try{

$conn->beginTransaction();

/* INSERT PROCESS */
$conn->prepare("
INSERT INTO processingbatch (BatchNo, InputQuantityKg, OutputQuantityKg, ProcessingDate, Status)
VALUES (?, ?, ?, ?, 'Completed')
")->execute([$batch, $input, $output, $date]);

/* UPDATE RAW STOCK (IMPORTANT PART) */
$conn->prepare("
UPDATE rawhoneystock 
SET QuantityAvailableKg = QuantityAvailableKg - ?
WHERE BatchNo = ?
")->execute([$input, $batch]);

/* UPDATE PROCESSED STOCK */
$stmt = $conn->prepare("SELECT ID FROM processedhoneystock WHERE BatchNo=?");
$stmt->execute([$batch]);

if($stmt->rowCount() > 0){

    $conn->prepare("
    UPDATE processedhoneystock
    SET QuantityAvailableKg = QuantityAvailableKg + ?
    WHERE BatchNo = ?
    ")->execute([$output, $batch]);

}else{

    $conn->prepare("
    INSERT INTO processedhoneystock (BatchNo, QuantityAvailableKg)
    VALUES (?, ?)
    ")->execute([$batch, $output]);
}

$conn->commit();

header("Location:index.php");

}catch(Exception $e){
    $conn->rollBack();
    echo "<div class='container'><div class='alert alert-danger'>❌ Error occurred</div></div>";
}

}
?>

<?php include("../../includes/footer.php"); ?>