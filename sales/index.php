<?php
ob_start();
include("../config/db.php");
include("../includes/layout.php");

// FETCH SALES WITH CUSTOMER + PAYMENTS
$sales = $conn->query("
    SELECT s.*, 
           c.Name as CustomerName,
           IFNULL(SUM(p.AmountPaid),0) as PaidAmount
    FROM sales s
    LEFT JOIN customers c ON s.CustomerID = c.CustomerID
    LEFT JOIN payments p ON s.SaleID = p.SaleID
    GROUP BY s.SaleID
    ORDER BY s.SaleID DESC
")->fetchAll();
?>

<div class="container mt-4">

<div class="d-flex justify-content-between mb-3">
    <h3>🛒 Sales </h3>
    <a href="create.php" class="btn btn-primary">+ New Sale</a>
</div>

<div class="card shadow rounded-4">
<div class="card-body">

<table id="salesTable" class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Customer</th>
<th>Total</th>
<th>Paid</th>
<th>Balance</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<?php foreach ($sales as $s): 
    $balance = $s['TotalAmount'] - $s['PaidAmount'];
?>
<tr>
    <td><?= $s['SaleID'] ?></td>

    <!-- ✅ UPDATED -->
    <td><?= $s['CustomerName'] ?></td>

    <td><?= number_format($s['TotalAmount'],2) ?></td>
    <td><?= number_format($s['PaidAmount'],2) ?></td>
    <td><?= number_format($balance,2) ?></td>

    <td>
        <span class="badge 
        <?= $s['PaymentStatus'] == 'Paid' ? 'bg-success' : 
           ($s['PaymentStatus'] == 'Partial' ? 'bg-warning' : 'bg-danger') ?>">
           <?= $s['PaymentStatus'] ?>
        </span>
    </td>

    <td><?= $s['SaleDate'] ?></td>

    <td>
        <a href="view.php?id=<?= $s['SaleID'] ?>" class="btn btn-info btn-sm">View</a>
        <a href="edit.php?id=<?= $s['SaleID'] ?>" class="btn btn-warning btn-sm">Edit</a>
        <a href="delete.php?id=<?= $s['SaleID'] ?>" 
           class="btn btn-danger btn-sm"
           onclick="return confirm('Delete this sale?')">
           Delete
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
    $('#salesTable').DataTable({
        pageLength: 5,
        order: [[0, "desc"]]
    });
});
</script>

<?php include("../includes/footer.php"); ?>