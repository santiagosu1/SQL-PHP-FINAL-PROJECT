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
            e.id AS event_id,
            e.title,
            e.event_date,
            e.venue,
            COUNT(t.id) AS total_orders,
            COALESCE(SUM(t.quantity), 0) AS total_tickets_sold,
            COALESCE(SUM(t.total_price), 0) AS total_revenue
        FROM events e
        LEFT JOIN tickets t ON e.id = t.event_id
        GROUP BY e.id, e.title, e.event_date, e.venue
        ORDER BY total_tickets_sold DESC, total_revenue DESC
    ");
    $stmt->execute();

    $results = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $results,
        'message' => 'Event sales summary retrieved successfully.'
    ]);

} catch (PDOException $e) {
    error_log('[reports/event_sales_summary] DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>