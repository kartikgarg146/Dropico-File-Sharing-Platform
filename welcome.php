<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION["username"];
$name     = $_SESSION["name"] ?? $username;  // for "Welcome, Name"

$conn = new mysqli("localhost", "root", "YOUR_PASSWORD_HERE", "dropico"); // Change this to your MySQL root password
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uploadDir = "uploads/";
$message   = "";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Function to generate random token for shareable links
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Upload file
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["shared_file"])) {
    $fileName   = basename($_FILES["shared_file"]["name"]);
    $targetPath = $uploadDir . $fileName;
    $allowedTypes = ['pdf', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($ext, $allowedTypes)) {
        if (move_uploaded_file($_FILES["shared_file"]["tmp_name"], $targetPath)) {
            $token = generateToken();

            // Insert with token (and download_count defaults to 0 in DB)
            $stmt = $conn->prepare("INSERT INTO files (filename, uploaded_by, token) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fileName, $username, $token);
            $stmt->execute();
            $stmt->close();

            $message = "File uploaded successfully.";
        } else {
            $message = "Failed to upload.";
        }
    } else {
        $message = "Invalid file type.";
    }
}

// Delete file
if (isset($_GET["delete"])) {
    $fileToDelete = basename($_GET["delete"]);

    $stmt = $conn->prepare("SELECT * FROM files WHERE filename=? AND uploaded_by=?");
    $stmt->bind_param("ss", $fileToDelete, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $filePath = $uploadDir . $fileToDelete;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $delStmt = $conn->prepare("DELETE FROM files WHERE filename=? AND uploaded_by=?");
        $delStmt->bind_param("ss", $fileToDelete, $username);
        $delStmt->execute();
        $delStmt->close();

        header("Location: welcome.php");
        exit;
    } else {
        $message = "You can only delete your own files.";
    }

    $stmt->close();
}

// Fetch all files
$filesResult = $conn->query("SELECT * FROM files ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard · Dropico</title>
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
                <div class="tagline">Manage your shared files</div>
            </div>
        </div>

        <div class="form-header">
            <!-- Now using full name -->
            <h1>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h1>
            <p>Upload files, generate shareable links, and manage your uploads from one place.</p>
        </div>

        <form enctype="multipart/form-data" method="POST">
            <div class="form-title">Share a File</div>
            <div class="form-subtitle">Choose a file and upload it to generate a link</div>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo ($message === "File uploaded successfully.") ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="shared_file">Select file</label>

                <!-- Bigger main file area -->
                <div class="file-drop">
                    <div class="file-drop-icon">⬆</div>
                    <div class="file-drop-text">
                        Drag & drop file here or <span>browse</span> from your system.
                        <div class="hint">Allowed: pdf, docx, jpg, png, txt · Max size as per server config</div>
                    </div>
                </div>

                <input type="file" id="shared_file" name="shared_file" required>
            </div>

            <button type="submit">Upload & Generate Link</button>

            <div class="extras">
                <span><span class="status-dot"></span> Storage: Local server</span>
                <span><a href="logout.php">Logout</a></span>
            </div>
        </form>

        <!-- Shared Files -->
        <div class="files-section">
            <h3>Shared Files</h3>

            <?php if ($filesResult->num_rows === 0): ?>
                <p style="font-size:12px;color:#6b6b81;margin-top:4px;">No files shared yet. Upload your first file above.</p>
            <?php else: ?>
                <ul>
                    <?php while ($row = $filesResult->fetch_assoc()): ?>
                        <?php
                            $downloads = isset($row['download_count']) ? (int)$row['download_count'] : 0;
                            $token     = isset($row['token']) ? $row['token'] : '';
                            $filename  = $row['filename'];
                        ?>
                        <li>
                            <div class="file-meta">
                                <span class="file-name">
                                    <!-- Preview / open in new tab -->
                                    <a href="uploads/<?php echo urlencode($filename); ?>" target="_blank">
                                        <?php echo htmlspecialchars($filename); ?>
                                    </a>
                                </span>
                                <span class="file-size">
                                    by <?php echo htmlspecialchars($row['uploaded_by']); ?>
                                    <?php if (!empty($row['uploaded_at'])): ?>
                                        · <?php echo htmlspecialchars($row['uploaded_at']); ?>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="file-actions">
                                <?php if (!empty($token)): ?>
                                    <!-- Shareable tracked download link -->
                                    <a href="download.php?token=<?php echo urlencode($token); ?>" target="_blank">
                                        Open share link
                                    </a>
                                <?php endif; ?>

                                <?php if ($row['uploaded_by'] === $username): ?>
                                    <a href="?delete=<?php echo urlencode($filename); ?>"
                                       onclick="return confirm('Delete this file?');">
                                        Delete
                                    </a>
                                <?php endif; ?>

                                <div class="file-downloads">
                                    Downloads: <?php echo $downloads; ?>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT SIDE IMAGE / INFO -->
    <div class="illustration-side">
        <div class="blob"></div>
        <div class="blob2"></div>

        <div class="hero-illustration">
            <img src="https://images.pexels.com/photos/1181671/pexels-photo-1181671.jpeg?auto=compress&cs=tinysrgb&w=1000"
                 alt="Dashboard illustration">
            <div class="hero-title">Your Dropico hub</div>
            <div class="hero-subtitle">
                Keep uploads organised, share links securely, and clean up old files anytime.
            </div>
        </div>

        <div class="highlights">
            <div class="highlight-card">
                <div class="highlight-title">Centralised sharing</div>
                <div>All your uploaded files in one dashboard, accessible after login.</div>
                <div class="highlight-pill">Simple & clean</div>
            </div>
            <div class="highlight-card">
                <div class="highlight-title">You’re in control</div>
                <div>Delete your own files instantly to revoke access.</div>
                <div class="highlight-pill">Owner controls</div>
            </div>
        </div>

        <div class="footer-info">
            <span>
                <div class="lock-icon">🔒</div>
                Private to registered users
            </span>
            <span>Dropico · PHP · MySQL · HTML · CSS</span>
        </div>
    </div>
</div>
</body>
</html>
