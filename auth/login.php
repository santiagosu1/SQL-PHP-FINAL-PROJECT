<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/User.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// Read and decode JSON body
$body = json_decode(file_get_contents('php://input'), true);

$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

// ── Input validation ──────────────────────────────────────────────────────────
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email format.']);
    exit;
}

// ── Sanitize ──────────────────────────────────────────────────────────────────
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

// ── Verify credentials ────────────────────────────────────────────────────────
try {
    $db   = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare(
        'SELECT id, email, password, first_name, last_name, role
         FROM users WHERE email = ?'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
        exit;
    }

    // ── Start session ─────────────────────────────────────────────────────────
    $loggedInUser = new User(
        (int)$user['id'], 
        $user['email'], 
        $user['first_name'], 
        $user['last_name'], 
        $user['role']
    );

    // 
    session_regenerate_id(true);          
    $_SESSION['user_id'] = $loggedInUser->getId(); 
    $_SESSION['role']    = $loggedInUser->getRole(); 

    echo json_encode([
        'success' => true,
        'data'    => [
            'id'         => $loggedInUser->getId(),
            'email'      => $loggedInUser->getEmail(),
            
            'full_name'  => $loggedInUser->getFullName(), 
            'role'       => $loggedInUser->getRole(),
            'is_admin'   => $loggedInUser->isAdmin() 
        ],
        'message' => 'Login successful.',
    ]);

} catch (PDOException $e) {
    error_log('[login] DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Login failed. Please try again.']);
}
