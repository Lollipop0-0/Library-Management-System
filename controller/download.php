<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get book ID from URL parameter
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id <= 0) {
    die("Invalid book ID");
}

// Fetch book information
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Book not found");
}

$book = $result->fetch_assoc();
$stmt->close();

// Check if book has PDF and if user has permission to download
if (empty($book['pdf_path'])) {
    die("No PDF available for this book");
}

// Security check: Regular users can only download approved books
$user_is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
if (!$user_is_admin && $book['status'] !== 'approved') {
    die("You don't have permission to download this book");
}

// Set uploads directory path
$upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$file_path = $upload_dir . $book['pdf_path'];

// Check if file exists
if (!file_exists($file_path)) {
    die("PDF file not found on server");
}

// Set headers for file download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($book['pdf_path']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Clear output buffer
flush();

// Read and output the file
readfile($file_path);

// Close connection
$conn->close();
exit;
?>