<?php 
ob_start();
include("../includes/layout.php"); 
include("../config/db.php"); ?>

<?php
if($_SERVER['REQUEST_METHOD']=="POST"){

$titles = $_POST['title'];
$categories = $_POST['category'];
$amounts = $_POST['amount'];
$dates = $_POST['date'];
$descriptions = $_POST['description'];

$sql = "INSERT INTO expenses (Title, Category, Amount, Description, ExpenseDate)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

for($i = 0; $i < count($titles); $i++){

    if(empty($titles[$i]) || empty($amounts[$i])) continue;

    $stmt->execute([
        $titles[$i],
        $categories[$i],
        $amounts[$i],
        $descriptions[$i],
        $dates[$i]
    ]);
}

header("Location: index.php");
}
?>

<div class="container mt-4">

<div class="card shadow-lg border-0">
<div class="card-header bg-dark text-white">
    <h5 class="mb-0">💸 Record Expenses</h5>
</div>

<div class="card-body">

<form method="POST" id="expenseForm">

<table class="table table-bordered" id="expenseTable">

<thead class="table-dark">
<tr>
    <th>Title</th>
    <th>Category</th>
    <th>Amount</th>
    <th>Date</th>
    <th>Description</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<tr>
<td><input type="text" name="title[]" class="form-control" required></td>

<td>
<select name="category[]" class="form-control" required>
    <option value="">Select</option>
    <option>Transport</option>
    <option>Salary</option>
    <option>Fuel</option>
    <option>Maintenance</option>
    <option>Other</option>
</select>
</td>

<td><input type="number" step="0.01" name="amount[]" class="form-control amount" required></td>

<td><input type="date" name="date[]" class="form-control" required></td>

<td><input type="text" name="description[]" class="form-control"></td>

<td>
<button type="button" class="btn btn-danger btn-sm removeRow">X</button>
</td>

</tr>

</tbody>
</table>

<!-- BUTTONS -->
<div class="d-flex justify-content-between">

<button type="button" id="addRow" class="btn btn-success">
    ➕ Add Row
</button>

<h5>Total: <span id="grandTotal">0</span> RWF</h5>

</div>

<br>

<button class="btn btn-primary w-100">
    💾 Save All Expenses
</button>

</form>

</div>
</div>

</div>

<script>
/* ADD ROW */
document.getElementById('addRow').addEventListener('click', function(){

let row = `
<tr>
<td><input type="text" name="title[]" class="form-control" required></td>

<td>
<select name="category[]" class="form-control" required>
<option value="">Select</option>
<option>Transport</option>
<option>Salary</option>
<option>Fuel</option>
<option>Maintenance</option>
<option>Other</option>
</select>
</td>

<td><input type="number" step="0.01" name="amount[]" class="form-control amount" required></td>

<td><input type="date" name="date[]" class="form-control" required></td>

<td><input type="text" name="description[]" class="form-control"></td>

<td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
</tr>
`;

document.querySelector('#expenseTable tbody').insertAdjacentHTML('beforeend', row);
});


/* REMOVE ROW */
document.addEventListener('click', function(e){
if(e.target.classList.contains('removeRow')){
    e.target.closest('tr').remove();
    calculateTotal();
}
});


/* CALCULATE TOTAL */
document.addEventListener('input', function(e){
if(e.target.classList.contains('amount')){
    calculateTotal();
}
});

function calculateTotal(){

let total = 0;

document.querySelectorAll('.amount').forEach(input=>{
    total += parseFloat(input.value) || 0;
});

document.getElementById('grandTotal').innerText = total.toFixed(2);
}
</script>

<?php include("../includes/footer.php"); ?>