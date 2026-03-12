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
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY purchase_date DESC');
        $stmt->execute([$user_id]);

        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data'    => $tickets,
            'message' => 'Tickets retrieved successfully.'
        ]);

    } catch (PDOException $e) {
        error_log('[tickets/list] DB error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error.']);
    }
?>