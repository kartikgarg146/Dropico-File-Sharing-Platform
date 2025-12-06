<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "YOUR_PASSWORD_HERE"; // Change this to your MySQL root password
$db   = "dropico"; // Database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Already logged in -> go to dashboard
if (isset($_SESSION["username"])) {
    header("Location: welcome.php");
    exit;
}

$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $errorMessage = "Please enter both username and password.";
    } else {
        // Fetch full name too so we can show "Welcome, Name"
        $stmt = $conn->prepare("SELECT name, username FROM users WHERE username=? AND password=? LIMIT 1");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $_SESSION["username"] = $row["username"];
            $_SESSION["name"]     = $row["name"]; // full name
            header("Location: welcome.php");
            exit;
        } else {
            $errorMessage = "Invalid login credentials.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login · Dropico</title>
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
                <div class="tagline">Fast · Simple · Secure file sharing</div>
            </div>
        </div>

        <div class="form-header">
            <h1>Welcome to Dropico</h1>
            <p>Login to upload files, generate links, and manage your shared content.</p>
        </div>

        <form method="POST" action="">
            <div class="form-title">Login</div>
            <div class="form-subtitle">Enter your credentials to access your dashboard</div>

            <?php if (!empty($errorMessage)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    placeholder="Enter your username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Enter your password"
                >
            </div>

            <button type="submit">Login</button>

            <div class="extras">
                <span><span class="status-dot"></span> Server: Online</span>
                <span>Secure access</span>
            </div>

            <a href="register.php">Don't have an account? <strong>Register</strong></a>
        </form>
    </div>

    <!-- RIGHT SIDE IMAGE / INFO -->
    <div class="illustration-side">
        <div class="blob"></div>
        <div class="blob2"></div>

        <div class="hero-illustration">
            <!-- You can change this image URL if you want -->
            <img src="https://images.pexels.com/photos/1181675/pexels-photo-1181675.jpeg?auto=compress&cs=tinysrgb&w=800"
                 alt="Team collaborating with files">
            <div class="hero-title">Dropico · Share files in seconds</div>
            <div class="hero-subtitle">
                Upload once and share securely with your friends, classmates, or team.
            </div>
        </div>

        <div class="highlights">
            <div class="highlight-card">
                <div class="highlight-title">End-to-end secured</div>
                <div>Your files stay private and under your control.</div>
                <div class="highlight-pill">No public indexing</div>
            </div>
            <div class="highlight-card">
                <div class="highlight-title">No setup required</div>
                <div>Just log in, upload, and share the link instantly.</div>
                <div class="highlight-pill">Ready to use</div>
            </div>
        </div>

        <div class="footer-info">
            <span>
                <div class="lock-icon">🔒</div>
                Dropico keeps your uploads safe
            </span>
            <span>Powered by PHP & MySQL</span>
        </div>
    </div>
</div>
</body>
</html>
