<?php
$host = "localhost";
$user = "root";
$pass = "YOUR_PASSWORD_HERE"; // Change this to your MySQL root password
$db   = "dropico"; // Database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = "";
$errorMessage   = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($name === "" || $username === "" || $email === "" || $password === "") {
        $errorMessage = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $username, $email, $password);

        if ($stmt->execute()) {
            $successMessage = "Registration successful. <a href='index.php'>Login here</a>.";
        } else {
            $errorMessage = "Error: Username might already exist.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register · Dropico</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-wrapper">
    <!-- LEFT SIDE -->
    <div class="form-side">
        <div class="logo">
            <div class="logo-icon">D</div>
            <div>
                <div class="logo-text">Dropico</div>
                <div class="tagline">Create an account to start sharing</div>
            </div>
        </div>

        <div class="form-header">
            <h1>Join Dropico</h1>
            <p>Quick registration. Start sharing files securely with your friends and team.</p>
        </div>

        <form method="POST" action="">
            <div class="form-title">Register</div>
            <div class="form-subtitle">Fill your basic details to get started</div>

            <?php if (!empty($successMessage)): ?>
                <div class="message success">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    placeholder="Your full name"
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        placeholder="Choose a username"
                    >
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        placeholder="you@example.com"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Create a strong password"
                >
                <div class="hint">Tip: Use a mix of letters, numbers & symbols.</div>
            </div>

            <button type="submit">Create Account</button>

            <div class="extras">
                <span><span class="status-dot"></span> Free for college use</span>
                <span>No credit card required</span>
            </div>

            <a href="index.php">Already have an account? <strong>Login</strong></a>
        </form>
    </div>

    <!-- RIGHT SIDE IMAGE / INFO -->
    <div class="illustration-side">
        <div class="blob"></div>
        <div class="blob2"></div>

        <div class="hero-illustration">
            <img src="https://images.pexels.com/photos/1181467/pexels-photo-1181467.jpeg?auto=compress&cs=tinysrgb&w=800"
                 alt="Person using a laptop">
            <div class="hero-title">One Dropico account. All your shares.</div>
            <div class="hero-subtitle">
                Keep your uploads organised and accessible from anywhere.
            </div>
        </div>

        <div class="highlights">
            <div class="highlight-card">
                <div class="highlight-title">Unlimited uploads*</div>
                <div>Share as many files as you want within your storage limit.</div>
                <div class="highlight-pill">Perfect for students & teams</div>
            </div>
            <div class="highlight-card">
                <div class="highlight-title">Access from any device</div>
                <div>Use Dropico from your laptop, tablet, or phone.</div>
                <div class="highlight-pill">No app required</div>
            </div>
        </div>

        <div class="footer-info">
            <span>
                <div class="lock-icon">🔒</div>
                Your data stays on your server
            </span>
            <span>Built with PHP · MySQL</span>
        </div>
    </div>
</div>
</body>
</html>
