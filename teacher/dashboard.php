<?php
$total_classes = 3;
$total_students = 45;
$absent_today = 5;

require_once '../auth/check_auth.php';
require_once '../config/db_connection.php';
require_role(['Teacher']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="/student_attendance_system/assets/style.css">
</head>
<body>
    <header class="app-header">
        <h1>Student Attendance Management System</h1>
    </header>

    <nav class="app-nav">
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="take_attendance.php">Take Attendance</a></li>
            <li><a href="myclass.php">My Classes</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="app-main">
        <section class="page-title">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Teacher'); ?></h2>
            <p>Review your class attendance summary for today.</p>
        </section>

        <section class="cards" aria-label="Attendance summary">
            <article class="card stat-card">
                <h3>Total Classes</h3>
                <p><?php echo $total_classes; ?></p>
            </article>

            <article class="card stat-card">
                <h3>Total Students</h3>
                <p><?php echo $total_students; ?></p>
            </article>

            <article class="card stat-card">
                <h3>Absent Today</h3>
                <p><?php echo $absent_today; ?></p>
            </article>
        </section>
    </main>

    <footer class="app-footer">
        <p>&copy; 2026 Student Attendance Management System</p>
    </footer>
</body>
</html>
