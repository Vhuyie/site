<?php 
include '../includes/db.php';
include '../includes/header.php';

$error = "";
$success = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Password validation
    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";
    if(!preg_match($pattern, $password)){
        $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, password, phone) VALUES (?, ?, ?, ?, ?)");
        if($stmt->execute([$first, $last, $email, $password_hash, $phone])){
            $success = "Customer added successfully!";
        } else {
            $error = "Error adding customer. Email may already exist.";
        }
    }
}
?>

<h2>Add New Customer</h2>
<?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if($success) echo "<p style='color:green;'>$success</p>"; ?>

<form method="POST">
    <label>First Name: <input type="text" name="first_name" required></label><br>
    <label>Last Name: <input type="text" name="last_name" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <label>Phone: <input type="text" name="phone"></label><br>
    <button type="submit">Add Customer</button>
</form>

<?php include '../includes/footer.php'; ?>
