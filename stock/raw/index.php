<?php
include("../../config/db.php");
include("../../includes/layout.php");

// GET STOCK
$stock = $conn->query("SELECT * FROM rawhoneystock LIMIT 1")->fetch();

// GET RECORDS
$stmt = $conn->query("
    SELECT r.*, s.Name 
    FROM rawhoney r
    LEFT JOIN suppliers s ON r.SupplierID = s.SupplierID
");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Raw Honey Stock</h2>

<div class="alert alert-success">
    <h4>Available Stock: <?= $stock['QuantityAvailableKg'] ?> Kg</h4>
</div>

<a href="create.php" class="btn btn-primary mb-3">Add Raw Honey</a>

<table id="rawTable" class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>Supplier</th>
            <th>Quantity (Kg)</th>
            <th>Price</th>
            <th>Total</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach($records as $r): ?>
        <tr>
            <td><?= $r['Name'] ?></td>
            <td><?= $r['QuantityKg'] ?></td>
            <td><?= $r['price'] ?></td>
            <td><?= $r['total'] ?></td>
            <td><?= $r['DateReceived'] ?></td>
            <td>
                <a href="edit.php?id=<?= $r['RawHoneyID'] ?>" class="btn btn-warning btn-sm">Edit</a>

                <a href="delete.php?id=<?= $r['RawHoneyID'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete?')">
                   Delete
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
$(document).ready(function () {
    $('#rawTable').DataTable();
});
</script>

<?php include("../../includes/footer.php"); ?>