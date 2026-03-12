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
        echo json_encode(['success' => false, 'error' => 'Access denied. Admin role required to create events.']);
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

    // Sanitization
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    $location = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');
    $venue = htmlspecialchars($venue, ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');

    // Basic validation
    if (empty($title) || empty($event_date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Title and event_date are required.']);
        exit;
    }

    // Validate date (YYYY-MM-DD)
    $d = DateTime::createFromFormat('Y-m-d', $event_date);
    if (!$d || $d->format('Y-m-d') !== $event_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid event_date format. Use YYYY-MM-DD.']);
        exit;
    }

    // Validate time if provided (HH:MM or HH:MM:SS)
    if (!empty($event_time)) {
        $tValid = preg_match('/^([01]?\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $event_time);
        if (!$tValid) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid event_time format. Use HH:MM or HH:MM:SS.']);
            exit;
        }
    }

    if ($price < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Price must be non-negative.']);
        exit;
    }

    if ($available_tick < 0 || $sold_tick < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Ticket counts must be non-negative integers.']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->connect();

        $new_id = 'evt-' . rand(10000, 99999);

        $stmt = $conn->prepare(
            'INSERT INTO events (
                id, title, description, event_date, event_time, location, venue, 
                price, category, available_tickets, sold_tickets
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $new_id, $title, $description, $event_date, $event_time, $location, $venue,
            $price, $category, $available_tick, $sold_tick
        ]);

        $stmtLog = $conn->prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, ?, ?, ?)'
        );
        $stmtLog->execute([$_SESSION['user_id'], 'CREATE', 'events', $new_id]);

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