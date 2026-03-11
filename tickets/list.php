<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use GET.']);
    exit;
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

try {
    $db = new Database();
    $conn = $db->connect();

    if ($user_id > 0) {
        $stmt = $conn->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY purchase_date DESC');
        $stmt->execute([$user_id]);
    } else {
        $stmt = $conn->query('SELECT * FROM tickets ORDER BY purchase_date DESC');
    }

    $tickets = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data'    => $tickets,
        'message' => 'Tickets have been getting correctly'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>