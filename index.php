<?php
ob_start();
require 'includes/helpers.php';
include("config/db.php");

// =======================
// HANDLE FORM SUBMIT
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    check_csrf(); // ✅ only run on POST

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {

        // Use correct connection variable ($conn)
        $stmt = $conn->prepare("
            SELECT id, username, password
            FROM users
            WHERE username = ?
        ");
        $stmt->execute([$username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            // 🔐 Secure session
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];

            header("Location: /honey-management-php/dashboard/index.php");
            exit;

        } else {
            $error = "Invalid username or password";
        }

    } else {
        $error = "Username and password are required";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | Honey Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #dbeafe, #eef2ff);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: "Poppins", sans-serif;
}

.login-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    padding: 2.5rem;
    width: 100%;
    max-width: 420px;
    text-align: center;
}

.login-logo img {
    width: 120px;
    height: 100px;
    object-fit: contain;
    margin-bottom: 1rem;
}

h3 {
    font-weight: 600;
    color: #1e3a8a;
    margin-bottom: 1.5rem;
}

.btn-primary {
    width: 100%;
    padding: 0.75rem;
    font-weight: 500;
    border-radius: 10px;
    background-color: #2563eb;
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #1d4ed8;
    transform: translateY(-2px);
}

.form-control {
    border-radius: 10px;
    padding: 0.75rem;
    border-color: #cbd5e1;
}

.form-control:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.25);
}

.alert {
    border-radius: 10px;
}

.footer-text {
    margin-top: 1.5rem;
    font-size: 0.9rem;
    color: #6b7280;
}
</style>
</head>

<body>
<div class="login-card">
    <div class="login-logo">
        <!-- Replace logo.png with your logo file -->
        <img src="assets/logo.PNG" alt="SOZO Logo">
    </div>
    <h3>System Login</h3>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
        <div class="mb-3 text-start">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter your username" required autofocus>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <div class="footer-text">&copy; <?= date('Y') ?> (HMS) SOZO ERP </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
