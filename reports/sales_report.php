<?php
include("../config/db.php");
include("../includes/layout.php");

// FILTER
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');

// QUERY
$data = $conn->query("
SELECT s.*, c.Name,
       IFNULL(SUM(p.AmountPaid),0) as PaidAmount
FROM sales s
LEFT JOIN customers c ON s.CustomerID=c.CustomerID
LEFT JOIN payments p ON s.SaleID=p.SaleID
WHERE s.SaleDate BETWEEN '$from' AND '$to'
GROUP BY s.SaleID
")->fetchAll();

// TOTALS
$totalSales = 0;
$totalPaid = 0;

foreach($data as $d){
    $totalSales += $d['TotalAmount'];
    $totalPaid += $d['PaidAmount'];
}

$balance = $totalSales - $totalPaid;
?>

<div class="container mt-4">

<h3>📊 Sales Report</h3>

<form class="row mb-3">
<div class="col-md-3">
<input type="date" name="from" value="<?= $from ?>" class="form-control">
</div>
<div class="col-md-3">
<input type="date" name="to" value="<?= $to ?>" class="form-control">
</div>
<div class="col-md-3">
<button class="btn btn-primary">Filter</button>
</div>
</form>

<div class="row text-center mb-4">
<div class="col-md-4">
<div class="alert alert-dark">Total Sales<br><?= number_format($totalSales,2) ?></div>
</div>
<div class="col-md-4">
<div class="alert alert-success">Total Paid<br><?= number_format($totalPaid,2) ?></div>
</div>
<div class="col-md-4">
<div class="alert alert-warning">Balance<br><?= number_format($balance,2) ?></div>
</div>
</div>

<table class="table table-bordered" id="reportTable">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Customer</th>
<th>Total</th>
<th>Paid</th>
<th>Balance</th>
<th>Status</th>
<th>Date</th>
</tr>
</thead>

<tbody>
<?php foreach($data as $d): ?>
<tr>
<td><?= $d['SaleID'] ?></td>
<td><?= $d['Name'] ?></td>
<td><?= number_format($d['TotalAmount'],2) ?></td>
<td><?= number_format($d['PaidAmount'],2) ?></td>
<td><?= number_format($d['TotalAmount'] - $d['PaidAmount'],2) ?></td>
<td><?= $d['PaymentStatus'] ?></td>
<td><?= $d['SaleDate'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>

<script>
$(document).ready(function(){
    $('#reportTable').DataTable();
});
</script>

<?php include("../includes/footer.php"); ?>