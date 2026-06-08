<?php
require_once '../auth/check_auth.php';
require_once '../config/db_connection.php';
require_role(['Teacher']);

$teacher_id = (int) $_SESSION['user_id'];
$class_id = isset($_GET['class_id']) ? (int) $_GET['class_id'] : 0;
$classes = [];
$students = [];
$selected_class = null;

try {
    $classStmt = $pdo->prepare(
        'SELECT * FROM class WHERE teacher_id = :teacher_id ORDER BY class_name'
    );
    $classStmt->execute(['teacher_id' => $teacher_id]);
    $classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($class_id > 0) {
        $selectedStmt = $pdo->prepare(
            'SELECT * FROM class WHERE class_id = :class_id AND teacher_id = :teacher_id'
        );
        $selectedStmt->execute([
            'class_id' => $class_id,
            'teacher_id' => $teacher_id,
        ]);
        $selected_class = $selectedStmt->fetch(PDO::FETCH_ASSOC);

        if ($selected_class) {
            $studentStmt = $pdo->prepare(
                'SELECT * FROM student WHERE class_id = :class_id ORDER BY first_name'
            );
            $studentStmt->execute(['class_id' => $class_id]);
            $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes</title>
    <link rel="stylesheet" href="/student_attendance_system/assets/style.css">
</head>
<body>
    <header class="app-header">
        <h1>Student Attendance Management System</h1>
    </header>

    <nav class="app-nav">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="take_attendance.php">Take Attendance</a></li>
            <li><a href="myclass.php" class="active">My Classes</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="app-main">
        <section class="page-title">
            <h2>My Classes</h2>
            <p>View your assigned classes and the students registered in each class.</p>
        </section>

        <?php if (count($classes) > 0): ?>
            <ul class="class-list">
                <?php foreach ($classes as $class): ?>
                    <li class="card class-item">
                        <span class="class-name">
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </span>
                        <div class="class-actions">
                            <a class="btn btn-secondary" href="myclass.php?class_id=<?php echo urlencode($class['class_id']); ?>">
                                View Students
                            </a>
                            <a class="btn" href="take_attendance.php?class_id=<?php echo urlencode($class['class_id']); ?>">
                                Take Attendance
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty-state">No classes assigned yet.</p>
        <?php endif; ?>

        <?php if ($class_id > 0): ?>
            <?php if (!$selected_class): ?>
                <p class="empty-state">Class not found or not assigned to you.</p>
            <?php else: ?>
                <section class="section-block">
                    <div class="page-title compact-title">
                        <h2><?php echo htmlspecialchars($selected_class['class_name']); ?> Students</h2>
                        <p><?php echo count($students); ?> student(s) registered in this class.</p>
                    </div>

                    <?php if (count($students) > 0): ?>
                        <div class="card attendance-form">
                            <div class="table-wrapper">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>Admission No</th>
                                            <th>Student Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $sn = 1; ?>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo $sn++; ?></td>
                                                <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="empty-state">No students registered in this class.</p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer class="app-footer">
        <p>&copy; 2026 Student Attendance Management System</p>
    </footer>
</body>
</html>
