<?php
    session_start();
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/database.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed. Use DELETE']);
        exit;
    }

    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required.']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true);
    $id = isset($body['id']) ? (int)$body['id'] : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Ticket ID is required to cancel.']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->connect();

        $stmtTicket = $conn->prepare('SELECT event_id, quantity FROM tickets WHERE id = ?');
        $stmtTicket->execute([$id]);
        $ticket = $stmtTicket->fetch();

        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Ticket not found.']);
            exit;
        }

        $event_id = $ticket['event_id'];
        $quantity = $ticket['quantity'];

        $conn->beginTransaction();

        $stmtDelete = $conn->prepare('DELETE FROM tickets WHERE id = ?');
        $stmtDelete->execute([$id]);

        // Audit log
        $stmtLog = $conn->prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, ?, ?, ?)'
        );
        $stmtLog->execute([$_SESSION['user_id'], 'DELETE', 'tickets', (string)$id]);

        $stmtUpdateEvent = $conn->prepare(
            'UPDATE events 
            SET available_tickets = available_tickets + ?, 
                sold_tickets = sold_tickets - ? 
            WHERE id = ?'
        );
        $stmtUpdateEvent->execute([$quantity, $quantity, $event_id]);

        $conn->commit();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Ticket cancelled successfully. Inventory was returned to the event.',
            'data' => [
                'deleted_ticket_id' => $id,
                'event_id' => $event_id,
                'tickets_returned' => $quantity
            ]
        ]);

    } catch (PDOException $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('[tickets/delete] DB error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error.']);
    }
?>