<?php
    session_start();
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/database.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed. Use GET.']);
        exit;
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required.']);
        exit;
    }

    $user_id = (int)$_SESSION['user_id'];

    try {
        $db   = new Database();
        $conn = $db->connect();

        // JOIN query — ticket details with event info
        $stmt = $conn->prepare(
            'SELECT t.id, t.event_id, t.quantity, t.total_price, t.purchase_date,
                    e.title AS event_title, e.event_date, e.venue, e.location
             FROM tickets t
             INNER JOIN events e ON e.id = t.event_id
             WHERE t.user_id = ?
             ORDER BY t.purchase_date DESC'
        );
        $stmt->execute([$user_id]);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Aggregate query — totals for this user
        $stmtSummary = $conn->prepare(
            'SELECT COUNT(*) AS total_orders,
                    SUM(quantity) AS total_tickets,
                    SUM(total_price) AS total_spent
             FROM tickets
             WHERE user_id = ?'
        );
        $stmtSummary->execute([$user_id]);
        $summary = $stmtSummary->fetch(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data'    => $tickets,
            'summary' => [
                'total_orders'  => (int)$summary['total_orders'],
                'total_tickets' => (int)$summary['total_tickets'],
                'total_spent'   => (float)$summary['total_spent'],
            ],
            'message' => 'Tickets retrieved successfully.'
        ]);

    } catch (PDOException $e) {
        error_log('[tickets/list] DB error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error.']);
    }
?>