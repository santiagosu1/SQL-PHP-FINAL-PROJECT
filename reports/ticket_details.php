<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use GET']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("
        SELECT 
            t.id AS ticket_id,
            u.id AS user_id,
            u.email,
            CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
            e.id AS event_id,
            e.title AS event_title,
            e.event_date,
            e.venue,
            t.quantity,
            t.total_price,
            t.purchase_date
        FROM tickets t
        INNER JOIN users u ON t.user_id = u.id
        INNER JOIN events e ON t.event_id = e.id
        ORDER BY t.purchase_date DESC
    ");
    $stmt->execute();

    $results = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $results,
        'message' => 'Ticket details retrieved successfully.'
    ]);

} catch (PDOException $e) {
    error_log('[reports/ticket_details] DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>