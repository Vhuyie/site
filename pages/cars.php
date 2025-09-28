<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- Handle Add Car ---
if (isset($_POST['add_car'])) {
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $reg = $_POST['registration_number'];
    $price = $_POST['price_per_day'];
    $status = 'Available';

    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../images/" . $image);
    }

    $stmt = $pdo->prepare("INSERT INTO cars (make, model, year, registration_number, price_per_day, status, image) VALUES (:make,:model,:year,:reg,:price,:status,:image)");
    $stmt->execute([
        ':make'=>$make,
        ':model'=>$model,
        ':year'=>$year,
        ':reg'=>$reg,
        ':price'=>$price,
        ':status'=>$status,
        ':image'=>$image
    ]);
}

// --- Handle Delete Car ---
if (isset($_GET['delete'])) {
    $car_id = $_GET['delete'];

    // Delete image file if exists
    $stmt = $pdo->prepare("SELECT image FROM cars WHERE car_id=:id");
    $stmt->execute([':id'=>$car_id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists("../images/".$img)) unlink("../images/".$img);

    $stmt = $pdo->prepare("DELETE FROM cars WHERE car_id=:id");
    $stmt->execute([':id'=>$car_id]);

    header("Location: cars.php");
    exit();
}

// --- Handle Edit Car ---
if (isset($_POST['edit_car'])) {
    $car_id = $_POST['car_id'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $reg = $_POST['registration_number'];
    $price = $_POST['price_per_day'];
    $status = $_POST['status'];

    $params = [':make'=>$make, ':model'=>$model, ':year'=>$year, ':reg'=>$reg, ':price'=>$price, ':status'=>$status, ':id'=>$car_id];
    $image_sql = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../images/".$image);
        $image_sql = ", image=:image";
        $params[':image'] = $image;
    }

    $stmt = $pdo->prepare("UPDATE cars SET make=:make, model=:model, year=:year, registration_number=:reg, price_per_day=:price, status=:status $image_sql WHERE car_id=:id");
    $stmt->execute($params);
}

// --- Fetch all cars ---
$stmt = $pdo->query("SELECT * FROM cars");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cars</title>
    
</head>
<body>

<h2>Cars</h2>

<table border="1">
    <tr>
        <th>ID</th><th>Make</th><th>Model</th><th>Year</th><th>Registration</th><th>Price/Day</th><th>Status</th><th>Image</th><th>Actions</th>
    </tr>
    <?php foreach($cars as $car): ?>
    <tr>
        <td><?= $car['car_id'] ?></td>
        <td><?= htmlspecialchars($car['make']) ?></td>
        <td><?= htmlspecialchars($car['model']) ?></td>
        <td><?= $car['year'] ?></td>
        <td><?= htmlspecialchars($car['registration_number']) ?></td>
        <td>R<?= $car['price_per_day'] ?></td>
        <td><?= $car['status'] ?></td>
        <td>
            <?php if($car['image']): ?>
                <img src="../images/<?= $car['image'] ?>" width="100" alt="Car Image">
            <?php endif; ?>
        </td>
        <td>
            <a href="#" class="edit-btn"
               data-id="<?= $car['car_id'] ?>"
               data-make="<?= htmlspecialchars($car['make'], ENT_QUOTES) ?>"
               data-model="<?= htmlspecialchars($car['model'], ENT_QUOTES) ?>"
               data-year="<?= $car['year'] ?>"
               data-reg="<?= htmlspecialchars($car['registration_number'], ENT_QUOTES) ?>"
               data-price="<?= $car['price_per_day'] ?>"
               data-status="<?= $car['status'] ?>">Edit</a> |
            <a href="cars.php?delete=<?= $car['car_id'] ?>" onclick="return confirm('Delete this car?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>Add New Car</h3>
<form method="POST" enctype="multipart/form-data">
    <label>Make:</label><input type="text" name="make" required><br>
    <label>Model:</label><input type="text" name="model" required><br>
    <label>Year:</label><input type="number" name="year" min="1990" max="<?= date('Y') ?>" required><br>
    <label>Registration Number:</label><input type="text" name="registration_number" required><br>
    <label>Price per Day:</label><input type="number" name="price_per_day" required><br>
    <label>Image:</label><input type="file" name="image" accept="image/*"><br>
    <button type="submit" name="add_car">Add Car</button>
</form>

<h3>Edit Car</h3>
<form method="POST" enctype="multipart/form-data" id="editForm">
    <input type="hidden" name="car_id" id="edit_car_id">
    <label>Make:</label><input type="text" name="make" id="edit_make" required><br>
    <label>Model:</label><input type="text" name="model" id="edit_model" required><br>
    <label>Year:</label><input type="number" name="year" id="edit_year" min="1990" max="<?= date('Y') ?>" required><br>
    <label>Registration Number:</label><input type="text" name="registration_number" id="edit_reg" required><br>
    <label>Price per Day:</label><input type="number" name="price_per_day" id="edit_price" required><br>
    <label>Status:</label>
    <select name="status" id="edit_status">
        <option value="Available">Available</option>
        <option value="Rented">Rented</option>
    </select><br>
    <label>Image:</label><input type="file" name="image" accept="image/*"><br>
    <button type="submit" name="edit_car">Update Car</button>
</form>

<script>
// JS to populate edit form safely
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('edit_car_id').value = this.dataset.id;
        document.getElementById('edit_make').value = this.dataset.make;
        document.getElementById('edit_model').value = this.dataset.model;
        document.getElementById('edit_year').value = this.dataset.year;
        document.getElementById('edit_reg').value = this.dataset.reg;
        document.getElementById('edit_price').value = this.dataset.price;
        document.getElementById('edit_status').value = this.dataset.status;
        window.scrollTo(0, document.body.scrollHeight);
    });
});
</script>
</body>
</html>
