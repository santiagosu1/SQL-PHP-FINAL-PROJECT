<?php
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/database.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed. Use PUT']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true);

    $id = trim($body['id'] ?? '');

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Event ID is required.']);
        exit;
    }

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
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
?>