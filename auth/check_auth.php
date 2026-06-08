<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

function require_role(array $allowedRoles): void
{
    $currentRole = strtolower(trim($_SESSION['role'] ?? ''));
    $allowedRoles = array_map(
        fn($role) => strtolower(trim($role)),
        $allowedRoles
    );

    if (!in_array($currentRole, $allowedRoles, true)) {
        header('Location: ../auth/login.php');
        exit();
    }
}
?>
