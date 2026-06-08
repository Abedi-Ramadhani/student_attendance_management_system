<?php
session_start();
require_once '../config/db_connection.php';

$error = '';
$success = '';
$username = '';
$selectedRoleId = '';

// Get all roles from the role table so the user can choose Admin or Teacher.
function getRoles(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT role_id, role_name FROM `role` ORDER BY role_name');
    return $stmt->fetchAll();
}

// Check if the username already exists before creating a new account.
function usernameExists(PDO $pdo, string $username): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$username]);
    return (int) $stmt->fetchColumn() > 0;
}

// Confirm that the selected role_id is really in the role table.
function roleExists(PDO $pdo, int $roleId): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM `role` WHERE role_id = ?');
    $stmt->execute([$roleId]);
    return (int) $stmt->fetchColumn() > 0;
}

// These roles are used to build the role dropdown in the HTML form.
$roles = getRoles($pdo);

// This block runs only after the user submits the registration form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect values from the form. The ?? '' part prevents errors if a field is missing.
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $selectedRoleId = $_POST['role_id'] ?? '';

    // Convert role_id to an integer. If it is not valid, this becomes false.
    $roleId = filter_var($selectedRoleId, FILTER_VALIDATE_INT);

    // Validate the form before saving anything to the database.
    if ($username === '' || $password === '' || $confirmPassword === '' || $selectedRoleId === '') {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 5) {
        $error = 'Username must be at least five characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least six characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif ($roleId === false || !roleExists($pdo, $roleId)) {
        $error = 'Please choose a valid role.';
    } elseif (usernameExists($pdo, $username)) {
        $error = 'That username is already taken.';
    } else {
        try {
            // A transaction keeps both inserts together. If one fails, both are cancelled.
            $pdo->beginTransaction();

            // Save a secure hashed password instead of saving the real password text.
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // First insert the new account into the users table.
            $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            $stmt->execute([$username, $passwordHash]);

            // Get the new user_id, then connect that user to the selected role.
            $userId = (int) $pdo->lastInsertId();
            $stmt = $pdo->prepare('INSERT INTO user_role (user_id, role_id) VALUES (?, ?)');
            $stmt->execute([$userId, $roleId]);

            // Save both inserts permanently.
            $pdo->commit();

            $success = 'Account created successfully. You can now log in.';
            $username = '';
            $selectedRoleId = '';
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Keep the database error private and show the user a simple message.
            $error = 'Account could not be created. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="/student_attendance_system/assets/style.css">
    <script src="/student_attendance_system/assets/validation.js"></script>
</head>
<body class="login-body">
    <main class="login-container">
        <form method="POST" class="login-form">
            <h2>Create Account</h2>

            <div class="input-box">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter username"
                    value="<?php echo htmlspecialchars($username); ?>"
                    required
                >
            </div>

            <div class="input-box">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>

            <div class="input-box">
                <label for="confirm_password">Confirm Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm password"
                    required
                >
            </div>

            <div class="input-box">
                <label for="role_id">Role</label>
                <select id="role_id" name="role_id" required>
                    <option value="">Select role</option>
                    <!-- Build the dropdown options from the role table. -->
                    <?php foreach ($roles as $role): ?>
                        <option
                            value="<?php echo (int) $role['role_id']; ?>"
                            <?php echo (string) $role['role_id'] === (string) $selectedRoleId ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">Create Account</button>

            <p class="auth-switch">
                Already have an account?
                <a href="/student_attendance_system/auth/login.php">Log in</a>
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
