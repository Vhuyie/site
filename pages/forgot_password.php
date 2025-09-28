<?php
session_start();
include '../includes/db.php'; // DB connection
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Optional: generate reset token & expiry
            $token = bin2hex(random_bytes(16));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update->execute([$token, $expiry, $email]);

            // In production: email reset link to user
            // $resetLink = "http://yourdomain.com/reset_password.php?token=" . $token;
        }

        // Always show success message (avoid leaking if email exists or not)
        $message = "If this email is registered, you’ll receive a password reset link shortly.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial, sans-serif; }
        form {
            max-width: 350px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        label { display: block; margin-top: 10px; }
        input[type="email"] {
            width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box;
        }
        button {
            margin-top: 15px; padding: 10px 15px;
            border: none; background-color: #007BFF; color: white;
            border-radius: 4px; cursor: pointer; width: 100%;
        }
        button:hover { background-color: #0056b3; }
        p.success { color: green; margin-top: 10px; }
        p.error { color: red; margin-top: 10px; }
        a { display: block; margin-top: 15px; text-align: center; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Forgot Password</h2>
    <form method="POST">
        <label>Enter your registered email:</label>
        <input type="email" name="email" required>
        <button type="submit">Submit</button>
        
        <?php if($message): ?>
            <p class="success"><?= htmlspecialchars($message) ?></p>
            <script>
                // redirect back to login.php after 3 seconds
                setTimeout(() => { window.location.href = "login.php"; }, 3000);
            </script>
        <?php endif; ?>

        <?php if($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
    <a href="login.php">Back to Login</a>
</body>
</html>
