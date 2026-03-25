<?php include("../config/db.php"); ?>
<?php include("../includes/layout.php"); ?>

<h3 class="mb-4">Dashboard</h3>

<?php
// RAW STOCK
$stmt = $conn->prepare("SELECT QuantityAvailableKg FROM rawhoneystock WHERE ID = 1");
$stmt->execute();
$raw = $stmt->fetch(PDO::FETCH_ASSOC)['QuantityAvailableKg'] ?? 0;

// PROCESSED STOCK
$stmt = $conn->prepare("SELECT QuantityAvailableKg FROM processedhoneystock WHERE ID = 1");
$stmt->execute();
$processed = $stmt->fetch(PDO::FETCH_ASSOC)['QuantityAvailableKg'] ?? 0;

// INVENTORY
$stmt = $conn->prepare("SELECT SUM(QuantityAvailable) as total FROM inventory");
$stmt->execute();
$packaged = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// SALES
$stmt = $conn->prepare("SELECT SUM(TotalAmount) as total FROM sales");
$stmt->execute();
$sales = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<div class="container">
    <div class="row">

        <!-- RAW STOCK -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Raw Honey Stock</h5>
                    <h2><?= $raw ?> Kg</h2>
                </div>
            </div>
        </div>

        <!-- PROCESSED STOCK -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Processed Honey</h5>
                    <h2><?= $processed ?> Kg</h2>
                </div>
            </div>
        </div>

        <!-- PACKAGED INVENTORY -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Packaged Inventory</h5>
                    <h2><?= $packaged ?></h2>
                </div>
            </div>
        </div>

        <!-- SALES -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Total Sales</h5>
                    <h2><?= $sales ?> RWF</h2>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include("../includes/footer.php"); ?>