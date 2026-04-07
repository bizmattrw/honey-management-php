<?php 
ob_start();
include("../../includes/layout.php"); 
include("../../config/db.php"); 
?>

<div class="container mt-4">

<div class="card shadow-lg border-0">
<div class="card-header bg-dark text-white">
    <h5 class="mb-0">⚙️ Processed Batches</h5>
</div>

<div class="card-body">
<a href="create.php" class="btn btn-primary mb-3">+ Record New</a>
<table id="table" class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
    <th>Batch No</th>
    <th>Input (Kg)</th>
    <th>Output (Kg)</th>
    <th>Efficiency (%)</th>
    <th>Date</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php
$sql = "SELECT * FROM processingbatch ORDER BY ProcessingDate DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();

$totalInput = 0;
$totalOutput = 0;

foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row):

$input = $row['InputQuantityKg'];
$output = $row['OutputQuantityKg'];

$efficiency = $input > 0 ? ($output / $input) * 100 : 0;

$totalInput += $input;
$totalOutput += $output;
?>

<tr>
    <td><?= $row['BatchNo'] ?></td>
    <td><?= number_format($input,2) ?></td>
    <td><?= number_format($output,2) ?></td>
    <td><?= number_format($efficiency,2) ?>%</td>
    <td><?= $row['ProcessingDate'] ?></td>
   <td>
    <a href="edit.php?id=<?= $row['BatchID'] ?>" class="btn btn-warning btn-sm">Edit</a>
    <a href="delete.php?id=<?= $row['BatchID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
</td>
</tr>

<?php endforeach; ?>

</tbody>

<tfoot>
<tr>
    <th>Total</th>
    <th><?= number_format($totalInput,2) ?></th>
    <th><?= number_format($totalOutput,2) ?></th>
    <th>
        <?php 
        $totalEff = $totalInput > 0 ? ($totalOutput / $totalInput) * 100 : 0;
        echo number_format($totalEff,2) . "%";
        ?>
    </th>
    <th colspan="2"></th>
</tr>
</tfoot>

</table>

</div>
</div>

</div>

<!-- ================= DATATABLE ================= -->
<script>
$(document).ready(function(){

$('#table').DataTable({

    pageLength: 10,

    lengthMenu: [
        [10, 100, 500, 1000, -1],
        [10, 100, 500, 1000, "All"]
    ],

    dom: 'Blfrtip',

    buttons: [
        'excelHtml5',
        'pdfHtml5'
    ]

});

});
</script>

<?php include("../../includes/footer.php"); ?>