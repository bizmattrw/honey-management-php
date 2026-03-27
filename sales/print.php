<?php
include("../config/db.php");

$id = $_GET['id'];

// SALE
$sale = $conn->query("
SELECT s.*, c.Name 
FROM sales s
LEFT JOIN customers c ON s.CustomerID=c.CustomerID
WHERE s.SaleID=$id
")->fetch();

// ITEMS
$items = $conn->query("
SELECT sd.*, p.Name, p.Size 
FROM saledetails sd
LEFT JOIN products p ON sd.ProductID=p.ProductID
WHERE sd.SaleID=$id
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>Invoice</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
body { font-family: Arial; }

.invoice-box {
    border: 1px solid #ddd;
    padding: 20px;
}

.logo {
    height: 80px;
}
</style>
</head>

<body onload="window.print()">

<div class="container mt-4">

<div class="invoice-box">

<!-- HEADER -->
<div class="row mb-4">
    <div class="col-md-6">
        <img src="../assets/logo.png" class="logo">
    </div>

    <div class="col-md-6 text-end">
        <h4>🐝 SOZO Honey </h4>
        <p>
            Kigali, Rwanda<br>
            Phone: +250 788684644<br>
            Email: info@honey.rw
        </p>
    </div>
</div>

<hr>

<!-- INVOICE INFO -->
<div class="row mb-3">
    <div class="col-md-6">
        <strong>Customer:</strong> <?= $sale['Name'] ?>
    </div>

    <div class="col-md-6 text-end">
        <strong>Invoice #:</strong> <?= $sale['SaleID'] ?><br>
        <strong>Date:</strong> <?= $sale['SaleDate'] ?>
    </div>
</div>

<!-- ITEMS -->
<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>Product</th>
<th>Quantity</th>
<th>Unit Price</th>
<th>Total</th>
</tr>
</thead>

<tbody>
<?php foreach($items as $i): ?>
<tr>
<td><?= $i['Name'] ?> (<?= $i['Size'] ?>)</td>
<td><?= $i['Quantity'] ?></td>
<td><?= number_format($i['UnitPrice'],2) ?></td>
<td><?= number_format($i['TotalPrice'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- TOTAL -->
<div class="text-end mt-3">
    <h4>Total: <?= number_format($sale['TotalAmount'],2) ?> RWF</h4>
</div>

<!-- FOOTER -->
<div class="mt-5 text-center">
    <p>Thank you for your valued partnership!!!</p>
</div>

</div>
</div>

</body>
</html>