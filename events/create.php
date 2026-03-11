<?php
    session_start();
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/database.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST']);
        exit;
    }

    // Protected endpoint: must be logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required.']);
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

    $created_by = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $stmt = $conn->prepare(
        'INSERT INTO events (id, title, description, event_date, event_time, location, venue, price, category, available_tickets, sold_tickets, created_by) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    
    $stmt->execute([$new_id, $title, $description, $event_date, $event_time, $location, $venue, $price, $category, $available_tick, $sold_tick, $created_by]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Event was created succefully.',
        'data'    => [
            'id'         => $new_id,
            'title'      => $title,
            'event_date' => $event_date
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>