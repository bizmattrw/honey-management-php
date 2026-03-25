<?php
$current_url = $_SERVER['REQUEST_URI'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Honey System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<!-- jQuery + DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<style>
body {
    overflow-x: hidden;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <span class="navbar-brand">🍯 Honey System</span>

    <div class="navbar-nav">

        <a class="nav-link <?= ($current_url == '../dashboard/index.php') ? 'active' : '' ?>" href="../dashboard/index.php">
            Dashboard
        </a>

        <a class="nav-link" href="/products/index.php">Products</a>

        <!-- STOCK DROPDOWN -->
        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                Stock
            </a>

            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/stock/raw/index.php">Raw Honey</a></li>
                <li><a class="dropdown-item" href="/stock/processed/index.php">Processing</a></li>
                <li><a class="dropdown-item" href="/stock/packaging/index.php">Packaging</a></li>
            </ul>
        </div>

        <a class="nav-link" href="/sales/index.php">Sales</a>

    </div>
</nav>