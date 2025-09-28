<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Handle Add Customer
if (isset($_POST['add_customer'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';

    // Server-side password validation
    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";
    if (!preg_match($pattern, $password)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } elseif ($first_name && $last_name && $email) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO customers (first_name, last_name, email, password) 
                               VALUES (:first_name, :last_name, :email, :password)");
        if ($stmt->execute([
            ':first_name' => $first_name,
            ':last_name'  => $last_name,
            ':email'      => $email,
            ':password'   => $password_hash
        ])) {
            $success = "Customer added successfully!";
        } else {
            $error = "Error adding customer. Email may already exist.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Handle Delete Customer
if (isset($_GET['delete'])) {
    $customer_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM customers WHERE customer_id = :id");
    $stmt->execute([':id'=>$customer_id]);
}

// Handle Search
$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search");
    $stmt->execute([':search'=>'%'.$search.'%']);
} else {
    $stmt = $pdo->query("SELECT * FROM customers");
}
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<h2>Customers</h2>

<?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if($success) echo "<p style='color:green;'>$success</p>"; ?>

<!-- Search Form -->
<form method="GET">
    <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<!-- Customers Table -->
<table border="1">
    <tr>
        <th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Actions</th>
    </tr>
    <?php foreach ($customers as $c): ?>
    <tr>
        <td><?= $c['customer_id'] ?></td>
        <td><?= htmlspecialchars($c['first_name']) ?></td>
        <td><?= htmlspecialchars($c['last_name']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td>
            <a href="customers.php?delete=<?= $c['customer_id'] ?>" onclick="return confirm('Delete this customer?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Add New Customer -->
<h3>Add New Customer</h3>
<form method="POST" id="customerForm">
    <label>First Name:</label><input type="text" name="first_name" required><br>
    <label>Last Name:</label><input type="text" name="last_name" required><br>
    <label>Email:</label><input type="email" name="email" required><br>
    <label>Password:</label><input type="password" name="password" id="password" required><br>
    <ul style="color:red;">
        <li id="ruleLength">Minimum 8 characters</li>
        <li id="ruleUpper">At least 1 uppercase letter</li>
        <li id="ruleLower">At least 1 lowercase letter</li>
        <li id="ruleNumber">At least 1 number</li>
        <li id="ruleSpecial">At least 1 special character</li>
    </ul>
    <button type="submit" name="add_customer">Add Customer</button>
</form>

<!-- Real-time password validation -->
<script>
const passwordInput = document.getElementById('password');
const rules = {
    length: document.getElementById('ruleLength'),
    upper: document.getElementById('ruleUpper'),
    lower: document.getElementById('ruleLower'),
    number: document.getElementById('ruleNumber'),
    special: document.getElementById('ruleSpecial')
};

passwordInput.addEventListener('input', function() {
    const val = passwordInput.value;
    rules.length.style.color = val.length >= 8 ? 'green' : 'red';
    rules.upper.style.color = /[A-Z]/.test(val) ? 'green' : 'red';
    rules.lower.style.color = /[a-z]/.test(val) ? 'green' : 'red';
    rules.number.style.color = /\d/.test(val) ? 'green' : 'red';
    rules.special.style.color = /[\W_]/.test(val) ? 'green' : 'red';
});

// Prevent submission if password is invalid
document.getElementById('customerForm').addEventListener('submit', function(e) {
    const val = passwordInput.value;
    const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (!pattern.test(val)) {
        e.preventDefault();
        alert("Password does not meet the required criteria.");
        passwordInput.focus();
    }
});
</script>

</body>
</html>
