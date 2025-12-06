<?php
// download.php - handles shareable links and download count

$host = "localhost";
$user = "root";
$pass = "YOUR_PASSWORD_HERE"; // Change this to your MySQL root password
$db   = "dropico"; // Database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uploadDir = "uploads/";

if (!isset($_GET['token']) || trim($_GET['token']) === "") {
    echo "Invalid download link.";
    exit;
}

$token = $_GET['token'];

// Find file by token
$stmt = $conn->prepare("SELECT filename FROM files WHERE token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Link expired or file not found.";
    exit;
}

$row      = $result->fetch_assoc();
$filename = $row['filename'];
$filePath = $uploadDir . $filename;

if (!file_exists($filePath)) {
    echo "File not found on server.";
    exit;
}

// Increase download count
$update = $conn->prepare("UPDATE files SET download_count = download_count + 1 WHERE token = ?");
$update->bind_param("s", $token);
$update->execute();
$update->close();

// Serve the file
$mimeType = function_exists('mime_content_type') ? mime_content_type($filePath) : 'application/octet-stream';

header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;
