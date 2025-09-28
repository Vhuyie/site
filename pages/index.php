<?php
session_start();
include '../includes/db.php'; // PDO connection
include '../includes/header.php';

// Protect page: only logged-in staff
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch summary stats using PDO
$total_cars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$available_cars = $pdo->query("SELECT COUNT(*) FROM cars WHERE status='Available'")->fetchColumn();
$rented_cars = $pdo->query("SELECT COUNT(*) FROM cars WHERE status='Rented'")->fetchColumn();
$total_customers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>SpeedyWheels Dashboard</title>
    
</head>
<body>
 

    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>!</h1>

    <h2>Summary Stats</h2>
    <ul>
        <li>Total Cars: <?= $total_cars ?></li>
        <li>Available Cars: <?= $available_cars ?></li>
        <li>Rented Cars: <?= $rented_cars ?></li>
        <li>Total Customers: <?= $total_customers ?></li>
    </ul>

</body>
</html>
