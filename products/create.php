<?php
ob_start();
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
                    <select name="Name" id="" class="form-control">
                        <option value="Glass">Glass</option>
                        <option value="Plastic">Plastic</option>
                        <option value="Single use">Single use</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Size (e.g 250ml, 500g)</label>
                    <select name="Size" id="" class="form-control">
                        <option value="60g">60g</option>
                        <option value="50g">50g</option>
                        <option value="80g">80g</option>
                        <option value="120g">120g</option>
                        <option value="150g">150g</option>
                        <option value="250g">250g</option>
                        <option value="300g">300g</option>
                        <option value="500g">500g</option>
                        <option value="1kg">1kg</option>
                        <option value="1.5kg">1.5kg</option>
                        <option value="3kg">3kg</option>
                        <option value="4kg">4kg</option>
                        <option value="7kg">7kg</option>
                        <option value="31kg">31kg</option>
                    </select>
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