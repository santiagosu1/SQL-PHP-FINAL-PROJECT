<?php
    session_start();
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/database.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed. Use PUT or POST.']);
        exit;
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required.']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true);

    $id             = isset($body['id']) ? trim($body['id']) : '';
    $title          = isset($body['title']) ? trim($body['title']) : '';
    $description    = isset($body['description']) ? trim($body['description']) : null;
    $event_date     = isset($body['event_date']) ? trim($body['event_date']) : '';
    $event_time     = isset($body['event_time']) ? trim($body['event_time']) : null;
    $location       = isset($body['location']) ? trim($body['location']) : null;
    $venue          = isset($body['venue']) ? trim($body['venue']) : null;
    $price          = isset($body['price']) ? (float)$body['price'] : null;
    $category       = isset($body['category']) ? trim($body['category']) : null;
    $available_tick = isset($body['available_tickets']) ? (int)$body['available_tickets'] : null;
    $sold_tick      = isset($body['sold_tickets']) ? (int)$body['sold_tickets'] : null;

    // Sanitization
    $id = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $description = $description !== null ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8') : null;
    $event_time = $event_time !== null ? htmlspecialchars($event_time, ENT_QUOTES, 'UTF-8') : null;
    $location = $location !== null ? htmlspecialchars($location, ENT_QUOTES, 'UTF-8') : null;
    $venue = $venue !== null ? htmlspecialchars($venue, ENT_QUOTES, 'UTF-8') : null;
    $category = $category !== null ? htmlspecialchars($category, ENT_QUOTES, 'UTF-8') : null;

    // Validation
    if (!preg_match('/^[A-Za-z0-9\-\_]+$/', $id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid event ID format.']);
        exit;
    }

    $d = DateTime::createFromFormat('Y-m-d', $event_date);
    if (!$d || $d->format('Y-m-d') !== $event_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid event_date format. Use YYYY-MM-DD.']);
        exit;
    }

    if ($price !== null && $price < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Price must be non-negative.']);
        exit;
    }

    if ($available_tick !== null && $available_tick < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'available_tickets must be non-negative.']);
        exit;
    }

    if ($sold_tick !== null && $sold_tick < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'sold_tickets must be non-negative.']);
        exit;
    }

    if (empty($id) || empty($title) || empty($event_date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID, title, and event_date are required.']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->connect();

        $stmtCheck = $conn->prepare('SELECT id FROM events WHERE id = ?');
        $stmtCheck->execute([$id]);
        
        if (!$stmtCheck->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Event not found.']);
            exit;
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied. Only admins can update events.']);
            exit;
        }

        $stmt = $conn->prepare(
            'UPDATE events 
             SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, venue = ?, price = ?, category = ?, available_tickets = ?, sold_tickets = ?
             WHERE id = ?'
        );
        
        $stmt->execute([
            $title, $description, $event_date, $event_time, $location, $venue, $price, $category, $available_tick, $sold_tick, 
            $id
        ]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Event not found or no new changes were made.']);
            exit;
        }

            // Audit log
        $stmtLog = $conn->prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, ?, ?, ?)'
        );
        $stmtLog->execute([$_SESSION['user_id'], 'UPDATE', 'events', $id]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Event was updated successfully.',
            'data'    => [
                'id'         => $id,
                'title'      => $title,
                'event_date' => $event_date
            ]
        ]);

    } catch (PDOException $e) {
        error_log('[events/update] DB error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error.']);
    }
?>