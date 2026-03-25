<?php
ob_start();
include("../../config/db.php");
include("../../includes/layout.php");

// STOCKS
$raw = $conn->query("SELECT QuantityAvailableKg FROM rawhoneystock LIMIT 1")->fetch();
$processed = $conn->query("SELECT QuantityAvailableKg FROM processedhoneystock LIMIT 1")->fetch();

// DATA
$data = $conn->query("SELECT * FROM processingbatch")->fetchAll();
?>

<h2>Processing Batches</h2>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="alert alert-danger">
            Raw Stock: <?= $raw['QuantityAvailableKg'] ?> Kg
        </div>
    </div>
    <div class="col-md-6">
        <div class="alert alert-success">
            Processed Stock: <?= $processed['QuantityAvailableKg'] ?> Kg
        </div>
    </div>
</div>

<a href="create.php" class="btn btn-primary mb-3">+ New Batch</a>

<table id="procTable" class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>Input (Raw Kg)</th>
            <th>Output (Processed Kg)</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach($data as $d): ?>
        <tr>
            <td><?= $d['InputQuantityKg'] ?></td>
            <td><?= $d['OutputQuantityKg'] ?></td>
            <td><?= $d['ProcessingDate'] ?></td>
            <td><?= $d['Status'] ?></td>
            <td>
                <a href="edit.php?id=<?= $d['BatchID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete.php?id=<?= $d['BatchID'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
$(function(){
    $('#procTable').DataTable();
});
</script>

<?php include("../../includes/footer.php"); ?>