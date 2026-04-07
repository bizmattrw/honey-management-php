<?php include("../includes/layout.php"); 
include("../config/db.php");

$id=$_GET['id'];

$data=$conn->query("SELECT * FROM expenses WHERE ExpenseID=$id")->fetch();
?>

<div class="container mt-4">
<h4>Edit Expense</h4>

<form method="POST">

<input type="text" name="title" value="<?= $data['Title'] ?>" class="form-control mb-2" required>
<input type="number" name="amount" value="<?= $data['Amount'] ?>" class="form-control mb-2" required>
<input type="date" name="date" value="<?= $data['ExpenseDate'] ?>" class="form-control mb-2" required>

<button class="btn btn-primary">Update</button>

</form>

<?php
if($_SERVER['REQUEST_METHOD']=="POST"){

$sql="UPDATE expenses SET Title=?,Amount=?,ExpenseDate=? WHERE ExpenseID=?";
$stmt=$conn->prepare($sql);
$stmt->execute([
    $_POST['title'],
    $_POST['amount'],
    $_POST['date'],
    $id
]);

echo "<div class='alert alert-success mt-2'>Updated</div>";
}
?>

</div>