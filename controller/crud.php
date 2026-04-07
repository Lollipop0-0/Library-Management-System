<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Set up uploads directory
$upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        die(json_encode(['status' => 'error', 'message' => 'Failed to create uploads directory']));
    }
    chmod($upload_dir, 0777);
}

if (!is_writable($upload_dir)) {
    chmod($upload_dir, 0777);
    if (!is_writable($upload_dir)) {
        die(json_encode(['status' => 'error', 'message' => 'Uploads directory is not writable. Please check permissions.']));
    }
}

$upload_dir = $upload_dir . DIRECTORY_SEPARATOR;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Authentication check - allow read access without login, but require auth for other actions
if ($action !== 'read' && $action !== '' && !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
    exit;
}

switch ($action) {
    case 'create':
        // CREATE: Submit a book for admin approval
        $title = $_POST['title'] ?? '';
        $author = $_POST['author'] ?? '';
        $publisher = $_POST['publisher'] ?? '';
        $genre = $_POST['genre'] ?? '';
        $description = $_POST['description'] ?? '';
        $pdf_path = null;
        
        // Get pricing information
        $is_free = isset($_POST['is_free']) ? intval($_POST['is_free']) : 1;
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.00;

        // Enhanced validation
        if (empty($title) || empty($author) || empty($publisher) || empty($genre) || empty($description)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }

        // Validate pricing for paid books
        if ($is_free == 0 && $price <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Price must be greater than 0 for paid books.']);
            exit;
        }

        // Handle PDF upload
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['pdf_file'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $file_size = $file['size'];
            $max_size = 10 * 1024 * 1024;

            if ($file_ext !== 'pdf' || $file['type'] !== 'application/pdf') {
                echo json_encode(['status' => 'error', 'message' => 'Only PDF files are allowed.']);
                exit;
            }
            if ($file_size > $max_size) {
                echo json_encode(['status' => 'error', 'message' => 'File size must be less than 10MB.']);
                exit;
            }

            $new_filename = 'book_' . time() . '_' . uniqid() . '.pdf';
            $pdf_path = $upload_dir . $new_filename;

            if (!move_uploaded_file($file['tmp_name'], $pdf_path)) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload PDF. Check directory permissions.']);
                exit;
            }
        }

        $publish_date = date('Y-m-d');
        
        // Set status to 'pending' for admin approval - INCLUDING PRICING FIELDS
        $sql = "INSERT INTO books (title, author, publisher, genre, description, publish_date, status, pdf_path, user_id, is_free, price) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database prepare failed: ' . $conn->error]);
            exit;
        }
        
        // Use relative path for database storage
        $pdf_db_path = $pdf_path ? basename($pdf_path) : null;
        $stmt->bind_param("sssssssiid", $title, $author, $publisher, $genre, $description, $publish_date, $pdf_db_path, $_SESSION['user_id'], $is_free, $price);

        if ($stmt->execute()) {
            $real_book_id = $conn->insert_id;
            
            // Rename file with actual book ID if PDF was uploaded
            if ($pdf_path) {
                $new_filename = 'book_' . $real_book_id . '_' . time() . '.pdf';
                $real_pdf_path = $upload_dir . $new_filename;
                
                if (rename($pdf_path, $real_pdf_path)) {
                    $update_sql = "UPDATE books SET pdf_path = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $new_filename, $real_book_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    $pdf_db_path = $new_filename;
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => "Book '{$title}' submitted for admin approval. It will be published after approval.",
                'book_id' => $real_book_id,
                'pdf_path' => $pdf_db_path
            ]);
        } else {
            // Clean up uploaded file if database insert failed
            if ($pdf_path && file_exists($pdf_path)) {
                unlink($pdf_path);
            }
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit book: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'read':
        $id = $_GET['id'] ?? null;
        $genre = $_GET['genre'] ?? null;
        // Use server-side session to check if user is admin
        $user_is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

        if ($id) {
            // Fetch specific book by ID
            $sql = "SELECT * FROM books WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $book = $result->fetch_assoc();
            
            if ($book) {
                // Security check: regular users can only see approved books by ID
                if (!$user_is_admin && $book['status'] !== 'approved') {
                    echo json_encode(['status' => 'error', 'message' => 'Book not found or not approved']);
                    $stmt->close();
                    break;
                }
                $books = [$book];
            } else {
                $books = [];
            }
            $stmt->close();
        } else {
            // Fetch books with appropriate filtering
            $user_id = $_GET['user_id'] ?? null;
            
            // Build base query
            if ($user_id) {
                // Viewing specific user's profile - only show their approved books unless it's the user themselves
                if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) {
                    // User viewing their own books - can see all their books
                    $sql = "SELECT * FROM books WHERE user_id = ?";
                    $params = [$user_id];
                    $types = "i";
                } else {
                    // Viewing another user's books - only show approved books
                    $sql = "SELECT * FROM books WHERE user_id = ? AND status = 'approved'";
                    $params = [$user_id];
                    $types = "i";
                }
            } else if ($user_is_admin) {
                // Admin can see all books
                $sql = "SELECT * FROM books WHERE 1=1";
                $params = [];
                $types = "";
            } else {
                // Regular users can only see approved books
                $sql = "SELECT * FROM books WHERE status = 'approved'";
                $params = [];
                $types = "";
            }
            
            // Add genre filter if specified
            if ($genre && !empty($genre) && $genre !== 'all') {
                $sql .= " AND genre = ?";
                $params[] = $genre;
                $types .= "s";
            }
            
            // Add order by
            $sql .= " ORDER BY created_at DESC";
            
            // Prepare and execute query
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            $stmt->close();
        }
        
        echo json_encode(['status' => 'success', 'data' => $books]);
        break;

    case 'approve_book':
        // ADMIN: Approve a pending book
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'Book ID is required']);
            exit;
        }

        $sql = "UPDATE books SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Book approved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to approve book: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'reject_book':
        // ADMIN: Reject a pending book
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'Book ID is required']);
            exit;
        }

        // Get PDF path before updating
        $pdf_sql = "SELECT pdf_path FROM books WHERE id = ?";
        $pdf_stmt = $conn->prepare($pdf_sql);
        $pdf_stmt->bind_param("i", $id);
        $pdf_stmt->execute();
        $pdf_result = $pdf_stmt->get_result()->fetch_assoc();
        $pdf_path = $pdf_result['pdf_path'] ?? null;
        $pdf_stmt->close();

        $sql = "UPDATE books SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Delete PDF file for rejected books
            if ($pdf_path && file_exists($upload_dir . $pdf_path)) {
                unlink($upload_dir . $pdf_path);
            }
            echo json_encode(['status' => 'success', 'message' => 'Book rejected and PDF deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to reject book: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'update':
        // UPDATE: Edit a book (admin only or owner with restrictions)
        $id = (int) ($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $author = $_POST['author'] ?? '';
        $publisher = $_POST['publisher'] ?? '';
        $genre = $_POST['genre'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'Book ID is required for update']);
            exit;
        }

        $updates = [];
        $params = [];
        $types = '';

        if (!empty($title)) {
            $updates[] = 'title = ?';
            $params[] = $title;
            $types .= 's';
        }
        if (!empty($author)) {
            $updates[] = 'author = ?';
            $params[] = $author;
            $types .= 's';
        }
        if (!empty($publisher)) {
            $updates[] = 'publisher = ?';
            $params[] = $publisher;
            $types .= 's';
        }
        if (!empty($genre)) {
            $updates[] = 'genre = ?';
            $params[] = $genre;
            $types .= 's';
        }
        if (!empty($description)) {
            $updates[] = 'description = ?';
            $params[] = $description;
            $types .= 's';
        }

        // Handle PDF upload
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['pdf_file'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($file_ext !== 'pdf' || $file['type'] !== 'application/pdf') {
                echo json_encode(['status' => 'error', 'message' => 'Only PDF files are allowed']);
                exit;
            }

            $max_size = 10 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                echo json_encode(['status' => 'error', 'message' => 'File size must be less than 10MB']);
                exit;
            }

            // Delete old PDF
            $old_pdf_query = "SELECT pdf_path FROM books WHERE id = ?";
            $stmt = $conn->prepare($old_pdf_query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($old_pdf = $result->fetch_assoc()) {
                if ($old_pdf['pdf_path'] && file_exists($upload_dir . $old_pdf['pdf_path'])) {
                    unlink($upload_dir . $old_pdf['pdf_path']);
                }
            }
            $stmt->close();

            // Upload new PDF
            $new_filename = 'book_' . $id . '_' . time() . '.pdf';
            $file_path = $upload_dir . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $updates[] = 'pdf_path = ?';
                $params[] = $new_filename;
                $types .= 's';
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload PDF file']);
                exit;
            }
        }

        if (empty($updates)) {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
            exit;
        }

        $params[] = $id;
        $types .= 'i';
        $sql = "UPDATE books SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Book updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update book: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'delete':
        // DELETE: Remove a book (admin only)
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID is required.']);
            exit;
        }

        // Get PDF path before delete
        $pdf_sql = "SELECT pdf_path FROM books WHERE id = ?";
        $pdf_stmt = $conn->prepare($pdf_sql);
        $pdf_stmt->bind_param("i", $id);
        $pdf_stmt->execute();
        $pdf_result = $pdf_stmt->get_result()->fetch_assoc();
        $pdf_path = $pdf_result['pdf_path'] ?? null;
        $pdf_stmt->close();

        $sql = "DELETE FROM books WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            if ($pdf_path && file_exists($upload_dir . $pdf_path)) {
                unlink($upload_dir . $pdf_path);
            }
            echo json_encode(['status' => 'success', 'message' => "Book ID {$id} and PDF deleted successfully."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete book: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'update_user_book':
        // UPDATE: User edits their own book
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $author = $_POST['author'] ?? '';
        $publisher = $_POST['publisher'] ?? '';
        $genre = $_POST['genre'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'Book ID is required for update']);
            exit;
        }

        // Verify the book belongs to the current user
        $check_sql = "SELECT user_id FROM books WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Book not found']);
            $check_stmt->close();
            exit;
        }
        
        $book_data = $check_result->fetch_assoc();
        if ($book_data['user_id'] != $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => 'You can only edit your own books']);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();

        $updates = [];
        $params = [];
        $types = '';

        if (!empty($title)) {
            $updates[] = 'title = ?';
            $params[] = $title;
            $types .= 's';
        }
        if (!empty($author)) {
            $updates[] = 'author = ?';
            $params[] = $author;
            $types .= 's';
        }
        if (!empty($publisher)) {
            $updates[] = 'publisher = ?';
            $params[] = $publisher;
            $types .= 's';
        }
        if (!empty($genre)) {
            $updates[] = 'genre = ?';
            $params[] = $genre;
            $types .= 's';
        }
        if (!empty($description)) {
            $updates[] = 'description = ?';
            $params[] = $description;
            $types .= 's';
        }

        // Handle PDF upload
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['pdf_file'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($file_ext !== 'pdf' || $file['type'] !== 'application/pdf') {
                echo json_encode(['status' => 'error', 'message' => 'Only PDF files are allowed']);
                exit;
            }

            $max_size = 10 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                echo json_encode(['status' => 'error', 'message' => 'File size must be less than 10MB']);
                exit;
            }

            // Delete old PDF
            $old_pdf_query = "SELECT pdf_path FROM books WHERE id = ?";
            $stmt = $conn->prepare($old_pdf_query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($old_pdf = $result->fetch_assoc()) {
                if ($old_pdf['pdf_path'] && file_exists($upload_dir . $old_pdf['pdf_path'])) {
                    unlink($upload_dir . $old_pdf['pdf_path']);
                }
            }
            $stmt->close();

            // Upload new PDF
            $new_filename = 'book_' . $id . '_' . time() . '.pdf';
            $file_path = $upload_dir . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $updates[] = 'pdf_path = ?';
                $params[] = $new_filename;
                $types .= 's';
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload PDF file']);
                exit;
            }
        }

        if (empty($updates)) {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
            exit;
        }

        // Reset status to pending after edit (for admin re-approval)
        $updates[] = 'status = ?';
        $params[] = 'pending';
        $types .= 's';

        $params[] = $id;
        $types .= 'i';
        $sql = "UPDATE books SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Book updated successfully and sent for re-approval']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update book: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'delete_user_book':
        // DELETE: User deletes their own book
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID is required.']);
            exit;
        }

        // Verify the book belongs to the current user
        $check_sql = "SELECT user_id, pdf_path FROM books WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Book not found']);
            $check_stmt->close();
            exit;
        }
        
        $book_data = $check_result->fetch_assoc();
        if ($book_data['user_id'] != $_SESSION['user_id'] && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])) {
            echo json_encode(['status' => 'error', 'message' => 'You can only delete your own books']);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();

        // Fetch PDF path before delete
        $pdf_path = $book_data['pdf_path'] ?? null;

        $sql = "DELETE FROM books WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            if ($pdf_path && file_exists($upload_dir . $pdf_path)) {
                unlink($upload_dir . $pdf_path);
            }
            echo json_encode(['status' => 'success', 'message' => "Book deleted successfully."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete book: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
}

$conn->close();
?>