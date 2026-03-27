<?php
include("../../config/db.php");
include("../../includes/layout.php");

// FETCH STOCKS
$processed = $conn->query("SELECT QuantityAvailableKg FROM processedhoneystock LIMIT 1")->fetch();

// FETCH PACKAGING DATA WITH PRODUCT NAME
$data = $conn->query("
    SELECT p.*, pr.Name, pr.Size
    FROM packaging p
    LEFT JOIN products pr ON p.ProductID = pr.ProductID
")->fetchAll();
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between mb-3">
        <h3>📦 Packaging Records</h3>
        <a href="create.php" class="btn btn-primary">+ New Packaging</a>
    </div>

    <!-- STOCK INFO -->
    <div class="alert alert-info">
        Available Processed Stock: <strong><?= $processed['QuantityAvailableKg'] ?> Kg</strong>
    </div>

    <div class="card shadow rounded-4">
        <div class="card-body">

            <table id="packagingTable" class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Processed (Kg)</th>
                        <th>Units</th>
                        <th>Date</th>
                       
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i=0; foreach ($data as $d): ?>
                    <tr><?php $i++; ?>
                        <td><?= $i?></td>
                        <td><?= $d['Name'] ?></td>
                        <td><?= $d['Size'] ?></td>
                        <td><?= $d['ProcessedUsedKg'] ?></td>
                        <td><?= $d['QuantityProduced'] ?></td>
                        <td><?= $d['PackagingDate'] ?></td>
                        
                        <td>
                            <a href="edit.php?id=<?= $d['PackagingID'] ?>" 
                               class="btn btn-warning btn-sm">✏️</a>

                            <a href="delete.php?id=<?= $d['PackagingID'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this record?')">
                               🗑️ 
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
$(document).ready(function () {
    $('#packagingTable').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        order: [[0, "desc"]]
    });
});
</script>

<?php include("../../includes/footer.php"); ?>