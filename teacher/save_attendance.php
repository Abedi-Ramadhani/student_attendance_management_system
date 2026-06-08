<?php
require_once '../auth/check_auth.php';
require_once '../config/db_connection.php';
require_role(['Teacher']);

// 2. Make sure this page was opened by submitting the attendance form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: take_attendance.php');
    exit();
}

// 3. Get the class id and the attendance status for each student.
$teacher_id = (int) $_SESSION['user_id'];
$class_id = isset($_POST['class_id']) ? (int) $_POST['class_id'] : 0;
$statuses = $_POST['status'] ?? [];

if ($class_id <= 0 || empty($statuses)) {
    header('Location: take_attendance.php');
    exit();
}

try {
    // 4. Confirm that this class belongs to the logged-in teacher.
    $classStmt = $pdo->prepare(
        'SELECT * FROM class WHERE class_id = :class_id AND teacher_id = :teacher_id'
    );
    $classStmt->execute([
        'class_id' => $class_id,
        'teacher_id' => $teacher_id,
    ]);
    $class = $classStmt->fetch(PDO::FETCH_ASSOC);

    if (!$class) {
        header('Location: take_attendance.php');
        exit();
    }

    $pdo->beginTransaction();

    // 5. Prepare the database queries used to save attendance.
    $studentCheckStmt = $pdo->prepare(
        'SELECT student_id FROM student WHERE student_id = :student_id AND class_id = :class_id'
    );

    $deleteStmt = $pdo->prepare(
        'DELETE FROM attendance WHERE student_id = :student_id AND attendance_date = CURDATE()'
    );

    $insertStmt = $pdo->prepare(
        'INSERT INTO attendance (student_id, attendance_date, status)
         VALUES (:student_id, CURDATE(), :status)'
    );

    // 6. Save each student's attendance for today.
    foreach ($statuses as $student_id => $status) {
        $student_id = (int) $student_id;

        // Change the form value into the value stored in the database.
        if ($status === 'P') {
            $attendance_status = 'Present';
        } else {
            $attendance_status = 'Absent';
        }

        $studentCheckStmt->execute([
            'student_id' => $student_id,
            'class_id' => $class_id,
        ]);
        $student = $studentCheckStmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            continue;
        }

        // Remove old attendance for today, then save the new one.
        $deleteStmt->execute([
            'student_id' => $student_id,
        ]);

        $insertStmt->execute([
            'student_id' => $student_id,
            'status' => $attendance_status,
        ]);
    }

    $pdo->commit();

    // 7. Go back to the same class attendance page with a success signal.
    header('Location: take_attendance.php?class_id=' . urlencode($class_id) . '&saved=1');
    exit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Error: ' . $e->getMessage());
}
?>
