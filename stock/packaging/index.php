<?php 
ob_start();
include("../../includes/layout.php"); 
include("../../config/db.php"); 
?>

<div class="container mt-4">

<div class="card shadow-lg border-0">

<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">📦 Packaging Records</h5>

    <!-- ✅ ADD NEW BUTTON -->
    <a href="create.php" class="btn btn-success">
        ➕ Add Packaging
    </a>
</div>

<div class="card-body">

<table id="table" class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
    <th>Batch No</th>
    <th>Product</th>
    <th>Size</th>
    <th>Quantity</th>
    <th>Used (Kg)</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
$sql = "
SELECT 
    p.PackagingID,
    p.BatchNo,   -- ✅ IMPORTANT FIX
    p.QuantityProduced,
    p.ProcessedUsedKg,
    p.PackagingDate,
    pr.Name,
    pr.Size
FROM packaging p
JOIN products pr ON p.ProductID = pr.ProductID
ORDER BY p.PackagingDate DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row):
?>

<tr>
    <!-- ✅ BATCHNO FIX -->
    <td><?= $row['BatchNo'] ?></td>

    <td><?= $row['Name'] ?></td>
    <td><?= $row['Size'] ?></td>
    <td><?= $row['QuantityProduced'] ?></td>
    <td><?= number_format($row['ProcessedUsedKg'],2) ?></td>
    <td><?= $row['PackagingDate'] ?></td>

    <td>
        <a href="edit.php?id=<?= $row['PackagingID'] ?>" class="btn btn-warning btn-sm">
            ✏️ Edit
        </a>

        <a href="delete.php?id=<?= $row['PackagingID'] ?>" 
           class="btn btn-danger btn-sm"
           onclick="return confirm('Delete this record?')">
            🗑 Delete
        </a>
    </td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>
</div>
</div>

<!-- DATATABLE -->
<script>
$(document).ready(function(){
    $('#table').DataTable({
        pageLength: 10,
        lengthMenu: [
            [10, 100, 500, 1000, -1],
            [10, 100, 500, 1000, "All"]
        ]
    });
});
</script>

<?php include("../../includes/footer.php"); ?>