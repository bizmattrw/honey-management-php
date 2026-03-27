<?php
$current_url = $_SERVER['REQUEST_URI'];
$base = "/honey-management-php/";
?>
<div id="sidebar" class="bg-dark text-white p-3 vh-100" style="width:250px; transition:0.3s;">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 id="logo">🍯 Honey</h5>
        <i class="fas fa-bars" style="cursor:pointer;" onclick="toggleSidebar()"></i>
    </div>

    <ul class="nav flex-column">

        <!-- DASHBOARD -->
        <li class="nav-item mb-2">
            <a class="nav-link text-white <?= strpos($current_url, 'dashboard') ? 'bg-secondary rounded' : '' ?>" 
               href="<?= $base ?>dashboard/index.php">
                <i class="fas fa-home"></i> <span class="text"> Dashboard</span>
            </a>
        </li>

        <!-- SUPPLIERS -->
        <li class="nav-item mb-2">
            <a class="nav-link text-white <?= strpos($current_url, 'suppliers') ? 'bg-secondary rounded' : '' ?>" 
               href="<?= $base ?>suppliers/index.php">
                <i class="fas fa-truck"></i> <span class="text"> Suppliers</span>
            </a>
        </li>

        <!-- CUSTOMERS -->
        <li class="nav-item mb-2">
            <a class="nav-link text-white <?= strpos($current_url, 'customers') ? 'bg-secondary rounded' : '' ?>" 
               href="<?= $base ?>customers/index.php">
                <i class="fas fa-users"></i> <span class="text"> Customers</span>
            </a>
        </li>

        <!-- PRODUCTS -->
        <li class="nav-item mb-2">
            <a class="nav-link text-white <?= strpos($current_url, 'products') ? 'bg-secondary rounded' : '' ?>" 
               href="<?= $base ?>products/index.php">
                <i class="fas fa-box"></i> <span class="text"> Products</span>
            </a>
        </li>

        <!-- STOCK -->
        <li class="nav-item mb-2">
            <div class="nav-link text-white d-flex justify-content-between" onclick="toggleStock()" style="cursor:pointer;">
                <span><i class="fas fa-warehouse"></i> <span class="text"> Stock</span></span>
                <i class="fas fa-chevron-down"></i>
            </div>

            <ul id="stockMenu" class="nav flex-column ms-3 mt-2 d-none">

                <li>
                    <a class="nav-link text-white" href="<?= $base ?>stock/raw/index.php">🍯 Raw Honey</a>
                </li>

                <li>
                    <a class="nav-link text-white" href="<?= $base ?>stock/processed/index.php">⚙️ Processing</a>
                </li>

                <li>
                    <a class="nav-link text-white" href="<?= $base ?>stock/packaging/index.php">📦 Packaging</a>
                </li>

            </ul>
        </li>

        <!-- SALES -->
        <li class="nav-item">
            <a class="nav-link text-white <?= strpos($current_url, 'sales') ? 'bg-secondary rounded' : '' ?>" 
               href="<?= $base ?>sales/index.php">
                <i class="fas fa-shopping-cart"></i> <span class="text"> Sales</span>
            </a>
        </li>

    </ul>
</div>

