<?php
$current_url = $_SERVER['REQUEST_URI'];
$base = "/honey-management-php/";

function isActive($url, $keyword){
    return strpos($url, $keyword) !== false ? 'active bg-secondary rounded' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Honey System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<style>
body {
    overflow-x: hidden;
}

/* Navbar Styling */
.navbar {
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.nav-link {
    margin-right: 10px;
    transition: 0.2s;
}

.nav-link:hover {
    background-color: rgba(255,255,255,0.1);
    border-radius: 5px;
}

/* Dropdown hover */
.dropdown-menu {
    border-radius: 10px;
}

.dropdown-item:hover {
    background-color: #f1f1f1;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">

    <!-- BRAND -->
    <a class="navbar-brand fw-bold" href="<?= $base ?>dashboard/index.php">
        🍯 Honey System
    </a>

    <!-- TOGGLER -->
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <!-- MENU -->
    <div class="collapse navbar-collapse" id="navbarNav">

        <ul class="navbar-nav ms-auto">

            <!-- DASHBOARD -->
            <li class="nav-item">
                <a class="nav-link <?= isActive($current_url, 'dashboard') ?>" href="<?= $base ?>dashboard/index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>

            <!-- PRODUCTS -->
            <li class="nav-item">
                <a class="nav-link <?= isActive($current_url, 'products') ?>" href="<?= $base ?>products/index.php">
                    <i class="fas fa-box"></i> Products
                </a>
            </li>

            <!-- STOCK -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?= isActive($current_url, 'stock') ?>" data-bs-toggle="dropdown">
                    <i class="fas fa-warehouse"></i> Stock
                </a>

                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= $base ?>stock/raw/index.php">🐝 Raw Honey</a></li>
                    <li><a class="dropdown-item" href="<?= $base ?>stock/processed/index.php">🏭 Processing</a></li>
                    <li><a class="dropdown-item" href="<?= $base ?>stock/packaging/index.php">📦 Packaging</a></li>
                </ul>
            </li>

            <!-- SALES -->
            <li class="nav-item">
                <a class="nav-link <?= isActive($current_url, 'sales') ?>" href="<?= $base ?>sales/index.php">
                    <i class="fas fa-shopping-cart"></i> Sales
                </a>
            </li>

            <!-- REPORTS DROPDOWN -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?= isActive($current_url, 'reports') ?>" data-bs-toggle="dropdown">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li><a class="dropdown-item" href="<?= $base ?>reports/sales_report.php">
                        📊 Sales Report
                    </a></li>

                    <li><a class="dropdown-item" href="<?= $base ?>reports/packaging_report.php">
                        📦 Packaging Report
                    </a></li>

                    <li><a class="dropdown-item" href="<?= $base ?>reports/processing_report.php">
                        🏭 Processing Report
                    </a></li>

                    <li><a class="dropdown-item" href="<?= $base ?>reports/raw_report.php">
                        🐝 Raw Honey Report
                    </a></li>

                    <li><a class="dropdown-item" href="<?= $base ?>reports/payment_report.php">
                        💰 Payment Report
                    </a></li>

                </ul>
            </li>

        </ul>

    </div>
</nav>