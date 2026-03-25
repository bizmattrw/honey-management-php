<?php
ob_start();
include("../../config/db.php");
include("../../includes/layout.php");

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM processingbatch WHERE BatchID=?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if ($_POST) {

    $newInput = $_POST['InputQuantityKg'];
    $newOutput = $_POST['OutputQuantityKg'];

    $diffInput = $newInput - $data['InputQuantityKg'];
    $diffOutput = $newOutput - $data['OutputQuantityKg'];

    // UPDATE
    $conn->prepare("
        UPDATE processingbatch
        SET InputQuantityKg=?, OutputQuantityKg=?, ProcessingDate=?, Status=?
        WHERE BatchID=?
    ")->execute([
        $newInput,
        $newOutput,
        $_POST['ProcessingDate'],
        $_POST['Status'],
        $id
    ]);

    // UPDATE STOCK
    $conn->prepare("
        UPDATE rawhoneystock 
        SET QuantityAvailableKg = QuantityAvailableKg - ?
    ")->execute([$diffInput]);

    $conn->prepare("
        UPDATE processedhoneystock 
        SET QuantityAvailableKg = QuantityAvailableKg + ?
    ")->execute([$diffOutput]);

    header("Location: index.php");
}
?>

<form method="POST">
    <input name="InputQuantityKg" value="<?= $data['InputQuantityKg'] ?>" class="form-control mb-2">
    <input name="OutputQuantityKg" value="<?= $data['OutputQuantityKg'] ?>" class="form-control mb-2">
    <input type="date" name="ProcessingDate" value="<?= $data['ProcessingDate'] ?>" class="form-control mb-2">
    <input name="Status" value="<?= $data['Status'] ?>" class="form-control mb-2">
    <button class="btn btn-warning">Update</button>
</form>

<?php include("../../includes/footer.php"); ?>