<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$user_id = (int)$_SESSION['user_id'];
$event_id = trim($body['event_id'] ?? '');
$quantity = isset($body['quantity']) ? (int)$body['quantity'] : 0;

if (empty($event_id) || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'event_id and a valid quantity are required.']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $stmtEvent = $conn->prepare('SELECT price, available_tickets, sold_tickets FROM events WHERE id = ?');
    $stmtEvent->execute([$event_id]);
    $event = $stmtEvent->fetch();

    if (!$event) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Event not found.']);
        exit;
    }

    if ($event['available_tickets'] < $quantity) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Not enough tickets available.']);
        exit;
    }

    $total_price = $event['price'] * $quantity;

    $conn->beginTransaction();

    $stmtTicket = $conn->prepare(
        'INSERT INTO tickets (user_id, event_id, quantity, total_price) VALUES (?, ?, ?, ?)'
    );
    $stmtTicket->execute([$user_id, $event_id, $quantity, $total_price]);

    $ticket_id = $conn->lastInsertId();

    // Audit log
    $stmtLog = $conn->prepare(
        'INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, ?, ?, ?)'
    );
    $stmtLog->execute([$user_id, 'CREATE', 'tickets', $ticket_id]);

    $new_available = $event['available_tickets'] - $quantity;
    $new_sold = $event['sold_tickets'] + $quantity;

    $stmtUpdateEvent = $conn->prepare(
        'UPDATE events SET available_tickets = ?, sold_tickets = ? WHERE id = ?'
    );
    $stmtUpdateEvent->execute([$new_available, $new_sold, $event_id]);

    $conn->commit();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Ticket purchased successfully!',
        'data' => [
            'ticket_id' => $ticket_id,
            'event_id' => $event_id,
            'quantity' => $quantity,
            'total_price' => $total_price
        ]
    ]);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('[tickets/create] DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>