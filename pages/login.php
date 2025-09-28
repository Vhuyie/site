<?php  
session_start();
include '../includes/db.php'; // still include in case you need DB later
$error = "";

// Predefined staff credentials
$predefined_email = "staff@speedywheels.za";
$predefined_password = "Staff123!"; // strong password
$predefined_name = "Staff Member";

// Function to validate strong password
function isStrongPassword($password) {
    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";
    return preg_match($pattern, $password);
}

// Initialize email variable to retain value
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate password strength
    if (!isStrongPassword($password)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } else {
        // Check against predefined credentials
        if ($email === $predefined_email && $password === $predefined_password) {
            $_SESSION['user_id'] = 1; // arbitrary ID for session
            $_SESSION['user_name'] = $predefined_name;
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Login</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        form {
            max-width: 350px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        .password-container {
            display: flex;
            align-items: center;
            width: 100%;
            margin-top: 5px;
        }
        .password-container input {
            flex: 1;
        }
        .toggle-password {
            margin-left: 5px;
            cursor: pointer;
            user-select: none;
            padding: 8px;
            background: #eee;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            margin-top: 15px;
            padding: 10px 15px;
            border: none;
            background-color: #007BFF;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        p.error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Employee Login</h2>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label>Password:</label>
        <div class="password-container">
            <input type="password" name="password" id="password" required>
            <span class="toggle-password" onclick="togglePassword()">Show</span>
        </div>

        <p style="text-align:center; margin-top:10px;">
    <a href="forgot_password.php">Forgot your password?</a>
</p>

        <button type="submit">Login</button>
        <?php if($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleText = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleText.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleText.textContent = 'Show';
            }
        }
    </script>
</body>
</html>
