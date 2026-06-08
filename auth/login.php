<?php
session_start();
require_once '../config/db_connection.php';

$error = '';
$success = '';

function getUserWithRole(PDO $pdo, string $username): ?array
{
    $stmt = $pdo->prepare(
        'SELECT u.*, r.role_name
         FROM users u
         LEFT JOIN user_role ur ON u.user_id = ur.user_id
         LEFT JOIN `role` r ON ur.role_id = r.role_id
         WHERE u.username = ?
         LIMIT 1'
    );
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function redirectForRole(string $role): string
{
    switch (strtolower(trim($role))) {
        case 'admin':
            return '/student_attendance_system/admin/dashboard.php';
        case 'teacher':
            return '/student_attendance_system/teacher/dashboard.php';
        default:
            return '/student_attendance_system/teacher/dashboard.php';
    }
}

function passwordMatches(string $enteredPassword, string $storedPassword): bool
{
    // New registered users have hashed passwords, so verify them with password_verify().
    if (password_get_info($storedPassword)['algo'] !== 0) {
        return password_verify($enteredPassword, $storedPassword);
    }

    // Existing accounts in your database may still have plain text passwords.
    return hash_equals($storedPassword, $enteredPassword);
}

// Show a simple message when the user comes from the logout page.
if (isset($_GET['logout'])) {
    $success = 'You have logged out successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif (strlen($username) < 5) {
        $error = 'Username must be at least five characters.';
    } else {
        $user = getUserWithRole($pdo, $username);

        if ($user && isset($user['password']) && passwordMatches($password, $user['password'])) {
            if (empty($user['role_name'])) {
                $error = 'Your account does not have a role assigned.';
            } else {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role_name'];

                header('Location: ' . redirectForRole($_SESSION['role']));
                exit();
            }
        }

        if ($error === '') {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="/student_attendance_system/assets/style.css">
    <script src="/student_attendance_system/assets/validation.js"></script>
</head>
<body class="login-body">
    <main class="login-container">
        <form method="POST" class="login-form">
            <h2>Student Attendance Management System</h2>

            <div class="input-box">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" required>
            </div>

            <div class="input-box">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>

            <button type="submit" name="login" class="btn">Login</button>

            <p class="auth-switch">
                Create account?
                <a href="/student_attendance_system/auth/register.php">Register here</a>
            </p>

            <?php if ($success !== ''): ?>
                <p class="alert alert-success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <p class="alert alert-error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </form>
    </main>
</body>
</html>
