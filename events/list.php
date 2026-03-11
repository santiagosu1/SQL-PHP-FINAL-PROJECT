<?php
    header('Content-Type: application/json');

    require_once __DIR__ . '/../config/database.php';

    if($_SERVER['REQUEST_METHOD'] !== 'GET'){
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed. Use GET']);
        exit;
    }

    try{
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare('SELECT * FROM events ORDER BY event_date ASC');
        $stmt->execute();

        $events = $stmt->fetchAll();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $events,
            'message' => 'Events got correctly.'
        ]);
    } catch(PDOException $e) {
        error_log('[events/list] DB error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
?>