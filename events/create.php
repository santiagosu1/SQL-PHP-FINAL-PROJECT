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

    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied. Admin role required to delete tickets.']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true);

    $title = trim($body['title'] ?? '');
    $description = trim($body['description'] ?? '');
    $event_date = trim($body['event_date'] ?? '');
    $event_time = trim($body['event_time'] ?? '');
    $price = isset($body['price']) ? (float)$body['price'] : 0.00;
    $location = trim($body['location'] ?? '');
    $venue = trim($body['venue'] ?? '');
    $category = trim($body['category'] ?? '');
    $available_tick = isset($body['available_tickets']) ? (int)$body['available_tickets'] : 0;
    $sold_tick = isset($body['sold_tickets']) ? (int)$body['sold_tickets'] : 0;

    if (empty($title) || empty($event_date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Title and event_date are required.']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->connect();

        $new_id = 'evt-' . rand(10000, 99999);
        $created_by = (int)$_SESSION['user_id'];

        $stmt = $conn->prepare(
            'INSERT INTO events (
                id, title, description, event_date, event_time, location, venue, 
                price, category, available_tickets, sold_tickets, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $new_id, $title, $description, $event_date, $event_time, $location, $venue,
            $price, $category, $available_tick, $sold_tick, $created_by
        ]);

        // Audit log
        $stmtLog = $conn->prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, ?, ?, ?)'
        );
        $stmtLog->execute([$created_by, 'CREATE', 'events', $new_id]);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Event was created successfully.',
            'data' => [
                'id' => $new_id,
                'title' => $title,
                'event_date' => $event_date
            ]
        ]);

    } catch (PDOException $e) {
        error_log('[events/create] DB error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error.']);
    }
?>