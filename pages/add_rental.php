<?php
include '../includes/db.php';
include '../includes/header.php';

// Fetch cars that are available
$cars = $conn->query("SELECT * FROM cars WHERE status='Available'")->fetchAll();

// Fetch all customers
$customers = $conn->query("SELECT * FROM customers")->fetchAll();

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $car_id = $_POST['car_id'];
    $customer_id = $_POST['customer_id'];
    $rental_date = $_POST['rental_date'];
    $return_date = $_POST['return_date'];

    // Calculate total amount (R500 per day)
    $days = (strtotime($return_date) - strtotime($rental_date)) / (60*60*24);
    $days = $days > 0 ? $days : 1; // Minimum 1 day
    $total_amount = $days * 500;

    // Insert rental
    $stmt = $conn->prepare("INSERT INTO rentals (car_id, customer_id, rental_date, return_date, status, total_amount) VALUES (?,?,?,?,?,?)");
    if($stmt->execute([$car_id, $customer_id, $rental_date, $return_date, 'Ongoing', $total_amount])){
        // Update car status to Rented
        $conn->prepare("UPDATE cars SET status='Rented' WHERE car_id=?")->execute([$car_id]);
        echo "<p>Rental added successfully! Total Amount: R$total_amount</p>";
    } else {
        echo "<p>Error adding rental.</p>";
    }
}
?>

<h2>Add New Rental</h2>
<form method="POST">
    <label>Car: 
        <select name="car_id" required>
            <option value="">Select Car</option>
            <?php foreach($cars as $car): ?>
                <option value="<?= $car['car_id'] ?>"><?= $car['make'] . ' ' . $car['model'] . ' (' . $car['registration_number'] . ')' ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Customer: 
        <select name="customer_id" required>
            <option value="">Select Customer</option>
            <?php foreach($customers as $c): ?>
                <option value="<?= $c['customer_id'] ?>"><?= $c['first_name'] . ' ' . $c['last_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Rental Date: <input type="date" name="rental_date" required></label><br>
    <label>Return Date: <input type="date" name="return_date" required></label><br>

    <button type="submit">Add Rental</button>
</form>

<?php include '../includes/footer.php'; ?>
