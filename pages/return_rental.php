<?php
include '../includes/db.php';
include '../includes/header.php';

if(!isset($_GET['id'])){
    echo "<p>No rental selected.</p>";
    exit;
}

$rental_id = $_GET['id'];

// Fetch rental details
$stmt = $conn->prepare("SELECT * FROM rentals WHERE rental_id=?");
$stmt->execute([$rental_id]);
$rental = $stmt->fetch();

if(!$rental){
    echo "<p>Rental not found.</p>";
    exit;
}

// Update rental status to Returned and set actual return date
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $actual_return_date = $_POST['actual_return_date'];

    // Recalculate total amount based on actual days
    $days = (strtotime($actual_return_date) - strtotime($rental['rental_date'])) / (60*60*24);
    $days = $days > 0 ? $days : 1;
    $total_amount = $days * 500;

    // Update rentals table
    $stmt = $conn->prepare("UPDATE rentals SET return_date=?, status='Returned', total_amount=? WHERE rental_id=?");
    $stmt->execute([$actual_return_date, $total_amount, $rental_id]);

    // Update car status to Available
    $conn->prepare("UPDATE cars SET status='Available' WHERE car_id=?")->execute([$rental['car_id']]);

    echo "<p>Rental returned successfully! Total Amount: R$total_amount</p>";
    echo '<p><a href="rentals.php">Back to Rentals</a></p>';
    exit;
}
?>

<h2>Return Rental</h2>
<p>Rental ID: <?= $rental['rental_id'] ?></p>
<p>Car ID: <?= $rental['car_id'] ?></p>
<p>Customer ID: <?= $rental['customer_id'] ?></p>
<p>Rental Date: <?= $rental['rental_date'] ?></p>
<p>Expected Return Date: <?= $rental['return_date'] ?></p>

<form method="POST">
    <label>Actual Return Date: <input type="date" name="actual_return_date" value="<?= date('Y-m-d') ?>" required></label><br>
    <button type="submit">Mark as Returned</button>
</form>

<?php include '../includes/footer.php'; ?>
