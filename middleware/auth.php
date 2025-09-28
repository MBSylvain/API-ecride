<?php
require_once __DIR__ . '/../config/session.php';

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Non authentifié']);
        exit;
    }
    return $_SESSION['user_id'];
}

function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }
    return null;
}
?>