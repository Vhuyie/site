<?php
session_start();
if(!isset($_SESSION['customer_id'])){
    header("Location: customer_login.php");
    exit;
}

include '../includes/db.php';
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

// Fetch customer rentals
$stmt = $conn->prepare("
    SELECT r.*, c.make, c.model, c.registration_number
    FROM rentals r
    JOIN cars c ON r.car_id = c.car_id
    WHERE r.customer_id=?
    ORDER BY r.rental_date DESC
");
$stmt->execute([$customer_id]);
$rentals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard - SpeedyWheels</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<h2>Welcome, <?= htmlspecialchars($customer_name) ?></h2>
<p><a href="customer_logout.php">Logout</a></p>

<h3>Your Rentals</h3>
<?php if(count($rentals) == 0): ?>
    <p>No rentals found.</p>
<?php else: ?>
<table border="1" cellpadding="5">
<tr>
    <th>Car</th>
    <th>Registration</th>
    <th>Rental Date</th>
    <th>Return Date</th>
    <th>Status</th>
    <th>Total Amount</th>
</tr>
<?php foreach($rentals as $r): ?>
<tr>
    <td><?= $r['make'] . ' ' . $r['model'] ?></td>
    <td><?= $r['registration_number'] ?></td>
    <td><?= $r['rental_date'] ?></td>
    <td><?= $r['return_date'] ?></td>
    <td><?= $r['status'] ?></td>
    <td>R<?= $r['total_amount'] ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</body>
</html>
