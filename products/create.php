<?php
include("../config/db.php");
include("../includes/layout.php");

if ($_POST) {

    $stmt = $conn->prepare("
        INSERT INTO products (Name, Size, Price)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $_POST['Name'],
        $_POST['Size'],
        $_POST['Price']
    ]);

    header("Location: index.php");
}
?>

<div class="container mt-4">
    <div class="card shadow rounded-4">
        <div class="card-header bg-dark text-white">
            <h4>📦 Add Product</h4>
        </div>

        <div class="card-body">
            <form method="POST">

                <div class="mb-3">
                    <label>Name</label>
                    <select name="Name" id="">
                        <option value="Honey Jar">Honey Jar</option>
                        <option value="Honey Bottle">Honey Bottle</option>
                        <option value="Honey Comb">Honey Comb</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Size (e.g 250ml, 500g)</label>
                    <input type="text" name="Size" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Price</label>
                    <input type="number" step="0.01" name="Price" class="form-control" required>
                </div>

                <button class="btn btn-success">Save</button>
                <a href="index.php" class="btn btn-secondary">Back</a>

            </form>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>