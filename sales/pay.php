<?php
ob_start();
include("../config/db.php");
include("../includes/layout.php");

$id = $_GET['id'];

// SALE
$sale = $conn->query("
SELECT s.*, c.Name 
FROM sales s
LEFT JOIN customers c ON s.CustomerID=c.CustomerID
WHERE s.SaleID=$id
")->fetch();

// PAYMENTS
$payments = $conn->query("
SELECT * FROM payments WHERE SaleID=$id
")->fetchAll();

$totalPaid = array_sum(array_column($payments, 'AmountPaid'));
$balance = $sale['TotalAmount'] - $totalPaid;

// HANDLE PAYMENT
if ($_POST && $balance > 0) {

    $amount = $_POST['Amount'];
    $method = $_POST['Method'];

    if ($amount > $balance) {
        echo "<div class='alert alert-danger'>❌ Amount exceeds balance!</div>";
    } else {

        $conn->prepare("
        INSERT INTO payments (SaleID, AmountPaid, PaymentDate, PaymentMethod)
        VALUES (?, ?, CURDATE(), ?)
        ")->execute([$id, $amount, $method]);

        // UPDATE STATUS
        $newPaid = $totalPaid + $amount;

        if ($newPaid >= $sale['TotalAmount']) {
            $status = "Paid";
        } elseif ($newPaid > 0) {
            $status = "Partial";
        } else {
            $status = "Pending";
        }

        $conn->query("UPDATE sales SET PaymentStatus='$status' WHERE SaleID=$id");

        echo "<div class='alert alert-success'>✅ Payment added!</div>";

        // REFRESH
        header("Refresh:1");
    }
}
?>

<div class="container mt-4">

<div class="card shadow">
<div class="card-header bg-success text-white">
    <h4>💰 Payment - Invoice #<?= $sale['SaleID'] ?></h4>
</div>

<div class="card-body">

<p><strong>Customer:</strong> <?= $sale['Name'] ?></p>

<div class="row text-center mb-3">
<div class="col-md-4">
<div class="alert alert-dark">Total: <?= number_format($sale['TotalAmount'],2) ?></div>
</div>
<div class="col-md-4">
<div class="alert alert-info">Paid: <?= number_format($totalPaid,2) ?></div>
</div>
<div class="col-md-4">
<div class="alert <?= $balance <= 0 ? 'alert-success':'alert-warning' ?>">
Balance: <?= number_format($balance,2) ?>
</div>
</div>
</div>

<!-- 🚫 HIDE FORM IF PAID -->
<?php if ($balance > 0): ?>

<form method="POST">
<div class="row">
<div class="col-md-4">
<input name="Amount" class="form-control" placeholder="Enter Amount" required>
</div>

<div class="col-md-4">
<select name="Method" class="form-select">
<option>Cash</option>
<option>Mobile Money</option>
<option>Bank</option>
</select>
</div>

<div class="col-md-4">
<button class="btn btn-success w-100">💾 Pay</button>
</div>
</div>
</form>

<?php else: ?>
<div class="alert alert-success text-center">
✅ This sale is fully paid
</div>
<?php endif; ?>

<hr>

<h5>📜 Payment History</h5>

<table class="table table-bordered">
<tr><th>Amount</th><th>Method</th><th>Date</th></tr>
<?php foreach($payments as $p): ?>
<tr>
<td><?= number_format($p['AmountPaid'],2) ?></td>
<td><?= $p['PaymentMethod'] ?></td>
<td><?= $p['PaymentDate'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<a href="view.php?id=<?= $id ?>" class="btn btn-secondary">⬅ Back</a>

</div>
</div>
</div>

<?php include("../includes/footer.php"); ?>