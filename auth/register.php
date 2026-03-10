<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// Read and decode JSON body
$body = json_decode(file_get_contents('php://input'), true);

$email      = trim($body['email']      ?? '');
$password   = trim($body['password']   ?? '');
$first_name = trim($body['first_name'] ?? '');
$last_name  = trim($body['last_name']  ?? '');

// ── Input validation ──────────────────────────────────────────────────────────
if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email format.']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
    exit;
}

// ── Sanitize strings ──────────────────────────────────────────────────────────
$email      = htmlspecialchars($email,      ENT_QUOTES, 'UTF-8');
$first_name = htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8');
$last_name  = htmlspecialchars($last_name,  ENT_QUOTES, 'UTF-8');

// ── Hash password ─────────────────────────────────────────────────────────────
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// ── Insert into database ──────────────────────────────────────────────────────
try {
    $db   = new Database();
    $conn = $db->connect();

    // Check if email already exists
    $check = $conn->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Email is already registered.']);
        exit;
    }

    $stmt = $conn->prepare(
        'INSERT INTO users (email, password, first_name, last_name, role)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$email, $hashed_password, $first_name, $last_name, 'user']);

    $new_id = $conn->lastInsertId();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'data'    => ['id' => (int)$new_id, 'email' => $email, 'role' => 'user'],
        'message' => 'User registered successfully.',
    ]);

} catch (PDOException $e) {
    error_log('[register] DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Registration failed. Please try again.']);
}
