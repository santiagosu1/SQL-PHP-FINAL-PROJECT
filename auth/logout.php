<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in.']);
    exit;
}

session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully.',
]);
