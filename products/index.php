<?php
include("../config/db.php");
include("../includes/layout.php");

$products = $conn->query("SELECT * FROM products")->fetchAll();
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between mb-3">
        <h3>📦 Products</h3>
        <a href="create.php" class="btn btn-primary">+ Add Product</a>
    </div>

    <table id="productsTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Size</th>
                <th>Price</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['ProductID'] ?></td>
                <td><?= $p['Name'] ?></td>
                <td><?= $p['Size'] ?></td>
                <td><?= $p['Price'] ?></td>
                <td><?= $p['CreatedAt'] ?></td>
                <td>
                    <a href="edit.php?id=<?= $p['ProductID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete.php?id=<?= $p['ProductID'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this product?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('#productsTable').DataTable();
});
</script>

<?php include("../includes/footer.php"); ?>