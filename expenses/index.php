<?php 
include("../includes/layout.php"); 
include("../config/db.php"); ?>

<div class="container mt-4">

<h4>Expenses</h4>

<a href="create.php" class="btn btn-primary mb-3">+ Add Expense</a>

<table id="table" class="table table-bordered">

<thead class="table-dark">
<tr>
<th>Title</th>
<th>Category</th>
<th>Amount</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php
$total = 0;

$sql="SELECT * FROM expenses ORDER BY ExpenseDate DESC";
foreach($conn->query($sql) as $row){

$total += $row['Amount'];
?>

<tr>
<td><?= $row['Title'] ?></td>
<td><?= $row['Category'] ?></td>
<td><?= $row['Amount'] ?></td>
<td><?= $row['ExpenseDate'] ?></td>
<td>
    <a href="edit.php?id=<?= $row['ExpenseID'] ?>" class="btn btn-sm btn-warning">Edit</a>
    <a href="delete.php?id=<?= $row['ExpenseID'] ?>" class="btn btn-sm btn-danger">Delete</a>
</td>
</tr>

<?php } ?>

</tbody>

<tfoot>
<tr>
<th colspan="2">Total</th>
<th><?= $total ?></th>
<th colspan="2"></th>
</tr>
</tfoot>

</table>

</div>

<script>
$(document).ready(function(){
    $('#table').DataTable({
        pageLength: 10,
        lengthMenu: [10,100,500,1000]
    });
});
</script>

<?php include("../includes/footer.php"); ?>