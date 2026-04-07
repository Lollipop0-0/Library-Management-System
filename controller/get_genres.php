<?php
session_start();
require_once '../db/db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    // Get all genres with book counts
    $query = "SELECT genre, COUNT(*) as count FROM books WHERE status = 'approved' GROUP BY genre ORDER BY count DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $genres = [];
    while ($row = $result->fetch_assoc()) {
        $genres[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $genres
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching genres: ' . $e->getMessage()
    ]);
}

$conn->close();
?>