<?php
require_once '../auth/check_auth.php';
require_once '../config/db_connection.php';
require_role(['admin']);

$total_users = 0;
$total_students = 0;
$total_classes = 0;

try {
    $total_users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $total_students = (int) $pdo->query('SELECT COUNT(*) FROM student')->fetchColumn();
    $total_classes = (int) $pdo->query('SELECT COUNT(*) FROM class')->fetchColumn();
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/student_attendance_system/assets/style.css">
</head>
<body>
    <header class="app-header">
        <h1>Student Attendance Management System</h1>
    </header>

    <nav class="app-nav">
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="app-main">
        <section class="page-title">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?></h2>
            <p>Review the system overview and manage attendance records.</p>
        </section>

        <section class="cards" aria-label="System summary">
            <article class="card stat-card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </article>

            <article class="card stat-card">
                <h3>Total Students</h3>
                <p><?php echo $total_students; ?></p>
            </article>

            <article class="card stat-card">
                <h3>Total Classes</h3>
                <p><?php echo $total_classes; ?></p>
            </article>
        </section>
    </main>

    <footer class="app-footer">
        <p>&copy; 2026 Student Attendance Management System</p>
    </footer>
</body>
</html>
