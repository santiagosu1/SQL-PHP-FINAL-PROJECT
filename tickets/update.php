<?php
    session_start();
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/database.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed. Use PUT or POST.']);
        exit;
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required.']);
        exit;
    }

    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied. Admin role required to update tickets.']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true);

    $ticket_id    = isset($body['ticket_id']) ? (int)$body['ticket_id'] : 0;
    $new_quantity = isset($body['quantity'])  ? (int)$body['quantity']  : 0;

    if ($ticket_id <= 0 || $new_quantity <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'A valid ticket_id and new quantity are required.']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->connect();

        $stmtTicket = $conn->prepare('SELECT event_id FROM tickets WHERE id = ?');
        $stmtTicket->execute([$ticket_id]);
        $ticket = $stmtTicket->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Ticket not found.']);
            exit;
        }

        $stmtEvent = $conn->prepare('SELECT price FROM events WHERE id = ?');
        $stmtEvent->execute([$ticket['event_id']]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        $new_total_price = $event['price'] * $new_quantity;

        $stmtUpdate = $conn->prepare('UPDATE tickets SET quantity = ?, total_price = ? WHERE id = ?');
        $stmtUpdate->execute([$new_quantity, $new_total_price, $ticket_id]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Ticket updated successfully by Admin.',
            'data'    => [
                'ticket_id'       => $ticket_id,
                'new_quantity'    => $new_quantity,
                'new_total_price' => $new_total_price
            ]
        ]);

    } catch (PDOException $e) {
        error_log('[tickets/update] DB error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error.']);
    }
?>