<?php 
ob_start();
include("../../includes/layout.php"); 
include("../../config/db.php");

$id = $_GET['id'];

/* FETCH RECORD */
$stmt = $conn->prepare("SELECT * FROM processingbatch WHERE BatchID=?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$data){
    echo "<div class='container mt-4'><div class='alert alert-danger'>Record not found</div></div>";
    exit;
}

$batch = $data['BatchNo'];

/* GET AVAILABLE STOCK */
$stmt = $conn->prepare("SELECT QuantityAvailableKg FROM rawhoneystock WHERE BatchNo=?");
$stmt->execute([$batch]);
$available = $stmt->fetchColumn();
?>

<div class="container mt-4">

<div class="card shadow-lg border-0">
<div class="card-header bg-warning text-dark">
    <h5 class="mb-0">✏️ Edit Processing Batch</h5>
</div>

<div class="card-body">

<form method="POST" id="form">

<div class="row g-3">

<!-- BATCH -->
<div class="col-md-6">
<label class="form-label">Batch No</label>
<input type="text" class="form-control bg-light" value="<?= $batch ?>" readonly>
</div>

<!-- AVAILABLE -->
<div class="col-md-6">
<label class="form-label">Available Raw Stock</label>
<input type="text" id="available" class="form-control bg-light" value="<?= $available ?>" readonly>
</div>

<!-- INPUT -->
<div class="col-md-6">
<label class="form-label">Input Quantity (Kg)</label>
<input type="number" step="0.01" name="input" id="input" class="form-control" value="<?= $data['InputQuantityKg'] ?>" required>
</div>

<!-- OUTPUT -->
<div class="col-md-6">
<label class="form-label">Output Quantity (Kg)</label>
<input type="number" step="0.01" name="output" id="output" class="form-control" value="<?= $data['OutputQuantityKg'] ?>" required>
</div>

<!-- DATE -->
<div class="col-md-6">
<label class="form-label">Processing Date</label>
<input type="date" name="date" class="form-control" value="<?= $data['ProcessingDate'] ?>" required>
</div>

<!-- EFFICIENCY -->
<div class="col-md-6">
<label class="form-label">Efficiency (%)</label>
<input type="text" id="efficiency" class="form-control bg-light" readonly>
</div>

</div>

<div id="alertBox" class="mt-3"></div>

<div class="d-flex justify-content-between mt-4">
    <a href="index.php" class="btn btn-secondary">⬅ Back</a>
    <button class="btn btn-warning">💾 Update</button>
</div>

</form>

</div>
</div>

</div>

<!-- ================= JS ================= -->
<script>
let inputField = document.getElementById('input');
let outputField = document.getElementById('output');
let available = parseFloat(document.getElementById('available').value) || 0;
let efficiencyField = document.getElementById('efficiency');
let alertBox = document.getElementById('alertBox');

/* CALCULATE EFFICIENCY */
function calculateEfficiency(){
    let input = parseFloat(inputField.value) || 0;
    let output = parseFloat(outputField.value) || 0;

    if(input > 0){
        let eff = (output / input) * 100;
        efficiencyField.value = eff.toFixed(2) + "%";
    }else{
        efficiencyField.value = "0%";
    }
}

/* VALIDATION */
function validate(){

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

/* EVENTS */
inputField.addEventListener('input', ()=>{
    calculateEfficiency();
    validate();
});

outputField.addEventListener('input', ()=>{
    calculateEfficiency();
    validate();
});

/* INIT */
calculateEfficiency();

/* SUBMIT */
document.getElementById('form').addEventListener('submit', function(e){
    if(!validate()){
        e.preventDefault();
    }
});
</script>

<?php
/* ================= BACKEND ================= */
if($_SERVER['REQUEST_METHOD']=="POST"){

$newInput  = floatval($_POST['input']);
$newOutput = floatval($_POST['output']);
$date      = $_POST['date'];

$oldInput  = $data['InputQuantityKg'];
$oldOutput = $data['OutputQuantityKg'];

try{

$conn->beginTransaction();

/* RESTORE OLD */
$conn->prepare("
UPDATE rawhoneystock 
SET QuantityAvailableKg = QuantityAvailableKg + ? 
WHERE BatchNo=?
")->execute([$oldInput, $batch]);

$conn->prepare("
UPDATE processedhoneystock 
SET QuantityAvailableKg = QuantityAvailableKg - ? 
WHERE BatchNo=?
")->execute([$oldOutput, $batch]);

/* CHECK AGAIN */
$stmt = $conn->prepare("SELECT QuantityAvailableKg FROM rawhoneystock WHERE BatchNo=?");
$stmt->execute([$batch]);
$availableNow = $stmt->fetchColumn();

if($newInput > $availableNow){
    throw new Exception("Not enough stock");
}

/* APPLY NEW */
$conn->prepare("
UPDATE rawhoneystock 
SET QuantityAvailableKg = QuantityAvailableKg - ? 
WHERE BatchNo=?
")->execute([$newInput, $batch]);

$conn->prepare("
UPDATE processedhoneystock 
SET QuantityAvailableKg = QuantityAvailableKg + ? 
WHERE BatchNo=?
")->execute([$newOutput, $batch]);

/* UPDATE RECORD */
$conn->prepare("
UPDATE processingbatch 
SET InputQuantityKg=?, OutputQuantityKg=?, ProcessingDate=? 
WHERE BatchID=?
")->execute([$newInput,$newOutput,$date,$id]);

$conn->commit();

echo "<div class='container mt-3'><div class='alert alert-success'>✅ Updated successfully</div></div>";

}catch(Exception $e){
$conn->rollBack();
echo "<div class='container'><div class='alert alert-danger'>❌ ".$e->getMessage()."</div></div>";
}

}
?>

<?php include("../../includes/footer.php"); ?>