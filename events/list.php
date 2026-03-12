<?php
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/database.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed. Use GET']);
        exit;
    }

    $id = isset($_GET['id']) ? trim($_GET['id']) : '';
    if ($id !== '') {
        $id = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        if (!preg_match('/^[A-Za-z0-9\-\_]+$/', $id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid event ID format.']);
            exit;
        }
    }

    try{
        $db = new Database();
        $conn = $db->connect();

        if ($id !== '') {
            
            $stmt = $conn->prepare('SELECT * FROM events WHERE id = ?');
            $stmt->execute([$id]);

            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Event not found.']);
                exit;
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $event,
                'message' => 'Event got correctly.'
            ]);

        } else {
            
            $stmt = $conn->prepare('SELECT * FROM events ORDER BY event_date ASC');
            $stmt->execute();

            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $events,
                'message' => 'Events got correctly.'
            ]);
        }

    } catch(PDOException $e) {
        error_log('[events/list] DB error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
?>