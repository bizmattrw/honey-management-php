<?php
ob_start();
include("../config/db.php");
include("../includes/layout.php");

$id = $_GET['id'];

// SALE + CUSTOMER
$sale = $conn->prepare("
SELECT s.*, c.Name 
FROM sales s
LEFT JOIN customers c ON s.CustomerID = c.CustomerID
WHERE s.SaleID=?
");
$sale->execute([$id]);
$sale = $sale->fetch();

// ITEMS
$items = $conn->prepare("
SELECT sd.*, p.Name, p.Size 
FROM saledetails sd
LEFT JOIN products p ON sd.ProductID = p.ProductID
WHERE sd.SaleID=?
");
$items->execute([$id]);
$items = $items->fetchAll();

// PAYMENTS
$payments = $conn->prepare("SELECT * FROM payments WHERE SaleID=?");
$payments->execute([$id]);
$payments = $payments->fetchAll();

$totalPaid = array_sum(array_column($payments, 'AmountPaid'));
$balance = $sale['TotalAmount'] - $totalPaid;
?>

<div class="container mt-4">

<div class="card shadow">
<div class="card-header bg-dark text-white">
    <h4>🧾 Invoice #<?= $sale['SaleID'] ?></h4>
</div>

<div class="card-body">

<p><strong>Customer:</strong> <?= $sale['Name'] ?></p>
<p><strong>Date:</strong> <?= $sale['SaleDate'] ?></p>

<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>Product</th>
<th>BatchNo</th>
<th>Qty</th>
<th>Price</th>
<th>Total</th>
</tr>
</thead>
<tbody>
<?php foreach($items as $i): ?>
<tr>
<td><?= $i['Name'] ?> (<?= $i['Size'] ?>)</td>
<td><?= $i['BatchNo'] ?> </td>
<td><?= $i['Quantity'] ?></td>
<td><?= number_format($i['UnitPrice'],2) ?></td>
<td><?= number_format($i['TotalPrice'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h5>Total: <?= number_format($sale['TotalAmount'],2) ?></h5>
<h5>Paid: <?= number_format($totalPaid,2) ?></h5>
<h5>Balance: <?= number_format($balance,2) ?></h5>

<hr>

<h5>💳 Payments</h5>
<table class="table table-bordered">
<tr><th>Amount</th><th>Method</th><th>Date</th></tr>
<?php foreach($payments as $p): ?>
<tr>
<td><?= $p['AmountPaid'] ?></td>
<td><?= $p['PaymentMethod'] ?></td>
<td><?= $p['PaymentDate'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<a href="print.php?id=<?= $id ?>" target="_blank" class="btn btn-secondary">🖨 Print</a>
<a href="pay.php?id=<?= $id ?>" class="btn btn-success">💰 Add Payment</a>

</div>
</div>
</div>

<?php include("../includes/footer.php"); ?>