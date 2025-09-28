<?php 
session_start();
include '../includes/db.php';
include '../includes/header.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Add Rental
if (isset($_POST['add_rental'])) {
    $car_id = $_POST['car_id'];
    $customer_id = $_POST['customer_id'];
    $rental_date = $_POST['start_date'];   // form field maps to rental_date
    $return_date = $_POST['end_date'];     // form field maps to return_date

    // Get the car's price_per_day from DB
// Fetch car price
$stmt = $pdo->prepare("SELECT price_per_day FROM cars WHERE car_id=:car_id");
$stmt->execute([':car_id' => $car_id]);
$price_per_day = $stmt->fetchColumn();

// Calculate days and total
$days = (strtotime($return_date) - strtotime($rental_date)) / 86400 + 1;
$total_amount = $price_per_day * $days;

// Insert rental
$stmt = $pdo->prepare("INSERT INTO rentals 
        (car_id, customer_id, rental_date, return_date, total_amount, status) 
        VALUES (:car_id, :customer_id, :rental_date, :return_date, :total_amount, 'Ongoing')");
    $stmt->execute([
        ':car_id' => $car_id,
        ':customer_id' => $customer_id,
        ':rental_date' => $rental_date,
        ':return_date' => $return_date,
        ':total_amount' => $total_amount
    ]);

    // Update car status
    $stmt = $pdo->prepare("UPDATE cars SET status='Rented' WHERE car_id=:id");
    $stmt->execute([':id' => $car_id]);
}

// Handle Return
if (isset($_GET['return'])) {
    $rental_id = $_GET['return'];

    // Set rental status to Returned
    $stmt = $pdo->prepare("UPDATE rentals SET status='Returned' WHERE rental_id=:id");
    $stmt->execute([':id' => $rental_id]);

    // Get car_id from rental
    $stmt = $pdo->prepare("SELECT car_id FROM rentals WHERE rental_id=:id");
    $stmt->execute([':id' => $rental_id]);
    $car_id = $stmt->fetchColumn();

    // Set car to Available
    $stmt = $pdo->prepare("UPDATE cars SET status='Available' WHERE car_id=:id");
    $stmt->execute([':id' => $car_id]);
}

// Fetch all rentals
$stmt = $pdo->query("
    SELECT 
        r.rental_id, 
        c.make, 
        c.model, 
        cu.first_name, 
        cu.last_name, 
        r.rental_date, 
        r.return_date, 
        r.total_amount, 
        r.status 
    FROM rentals r
    JOIN cars c ON r.car_id = c.car_id
    JOIN customers cu ON r.customer_id = cu.customer_id
");
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch cars and customers for dropdowns
$cars = $pdo->query("SELECT * FROM cars WHERE status='Available'")->fetchAll(PDO::FETCH_ASSOC);
$customers = $pdo->query("SELECT * FROM customers")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rentals</title>
</head>
<body>

<h2>Rentals</h2>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Car</th>
        <th>Customer</th>
        <th>Start</th>
        <th>End</th>
        <th>Price</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($rentals as $r): ?>
    <tr>
        <td><?= $r['rental_id'] ?></td>
        <td><?= htmlspecialchars($r['make'].' '.$r['model']) ?></td>
        <td><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></td>
        <td><?= $r['rental_date'] ?></td>
        <td><?= $r['return_date'] ?></td>
        <td>R<?= $r['total_amount'] ?></td>
        <td><?= $r['status'] ?></td>
        <td>
            <?php if ($r['status'] == 'Ongoing'): ?>
                <a href="rentals.php?return=<?= $r['rental_id'] ?>">Mark Returned</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>Add New Rental</h3>
<form method="POST" id="rentalForm">
    <label>Car:</label>
<select name="car_id" id="car_id" required>
    <?php foreach ($cars as $car): ?>
        <option value="<?= $car['car_id'] ?>" data-price="<?= $car['price_per_day'] ?>">
            <?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?> 
            (R<?= $car['price_per_day'] ?>/day)
        </option>
    <?php endforeach; ?>
</select><br>


    <label>Customer:</label>
    <select name="customer_id" required>
        <option value="">Select Customer</option>
        <?php foreach ($customers as $c): ?>
            <option value="<?= $c['customer_id'] ?>">
                <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Start Date:</label>
    <input type="date" name="start_date" id="start_date" required><br>

    <label>End Date:</label>
    <input type="date" name="end_date" id="end_date" required><br>

    <label>Total Price : R</label>
    <input type="text" id="total_price" value="0" readonly><br>

    <button type="submit" name="add_rental">Add Rental</button>
</form>


<script>
const startInput = document.getElementById('start_date');
const endInput = document.getElementById('end_date');
const carSelect = document.getElementById('car_id');
const priceInput = document.getElementById('total_price');

function calculatePrice() {
    const start = new Date(startInput.value);
    const end = new Date(endInput.value);

    if (start && end && end >= start) {
        const days = (end - start) / (1000 * 60 * 60 * 24) + 1;

        const selectedOption = carSelect.options[carSelect.selectedIndex];
        const ratePerDay = parseFloat(selectedOption.getAttribute('data-price')) || 0;

        priceInput.value = (days * ratePerDay).toFixed(2);
    } else {
        priceInput.value = 0;
    }
}

startInput.addEventListener('change', calculatePrice);
endInput.addEventListener('change', calculatePrice);
carSelect.addEventListener('change', calculatePrice);
</script>


</body>
</html>
