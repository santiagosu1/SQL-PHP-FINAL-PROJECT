<?php
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/database.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed. Use DELETE']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true);
    $id = trim($body['id'] ?? '');

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Event ID is required to delete.']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare('DELETE FROM events WHERE id = ?');

        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Event not found.']);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Event was deleted successfully.',
            'data'    => ['id' => $id]
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
?>