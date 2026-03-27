<?php
include("../config/db.php");

$id = $_GET['id'];

$conn->prepare("DELETE FROM products WHERE ProductID=?")->execute([$id]);

header("Location: index.php");