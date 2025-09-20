<?php
require_once __DIR__ . '/classes/user.php';
require_once __DIR__ . '/classes/student.php';
require_once __DIR__ . '/classes/attendance.php';

$user = new User();
$user->startSession();

if (!$user->isLoggedIn() || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student = new Student();
$attendance = new Attendance();

$studentId = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Student';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_excuse'])) {
    $courseId = (int) $_POST['course_id'];
    $reason = trim($_POST['reason']);
    $filePath = null;

    // Handle file upload
    if (!empty($_FILES['file']['name'])) {
        $uploadDir = __DIR__ . "/uploads/excuses/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES['file']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            $filePath = "uploads/excuses/" . $fileName;
        }
    }

    if ($attendance->submitExcuseLetter($studentId, $courseId, $reason, $filePath)) {
        $_SESSION['success'] = "Excuse letter submitted successfully. Waiting for approval.";
    } else {
        $_SESSION['error'] = "Failed to submit excuse letter. Try again.";
    }

    header("Location: student_dashboard.php");
    exit;
}

// Handle check-in / check-out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['course_id'])) {
    $courseId = (int) $_POST['course_id'];
    $today = date("Y-m-d");

    if ($_POST['action'] === 'check_in') {
        $todayRecord = $attendance->getAttendanceByDate($studentId, $courseId, $today);
        if ($todayRecord) {
            $_SESSION['error'] = "You already have an attendance record for today in this course.";
        } else {
            $attendance->logAttendance($studentId, $courseId, 'Present', date("H:i:s"), null);
            $_SESSION['success'] = "Checked in successfully.";
        }
    } elseif ($_POST['action'] === 'check_out') {
        $todayRecord = $attendance->getAttendanceByDate($studentId, $courseId, $today);
        if (!$todayRecord) {
            $_SESSION['error'] = "No check-in found for today in this course.";
        } elseif (!empty($todayRecord['check_out'])) {
            $_SESSION['error'] = "You already checked out for this course today.";
        } else {
            $attendance->updateAttendance($todayRecord['id'], 'Present', date("H:i:s"));
            $_SESSION['success'] = "Checked out successfully.";
        }
    }

    header("Location: student_dashboard.php");
    exit;
}

$courses = $student->getEnrolledCourses($studentId);
$history = $attendance->getAttendanceHistory($studentId);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        accent: '#3B82F6',
                        lightBg: '#F9FAFB',
                        cardBg: '#FFFFFF',
                        textDark: '#111827',
                        textMuted: '#6B7280'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-lightBg text-textDark min-h-screen">
    <!-- Top Navbar -->
    <header class="bg-cardBg shadow-md sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-accent">Student Dashboard</h1>
            <div class="flex items-center gap-4">
                <span class="hidden sm:block text-textMuted">Welcome, <b><?= htmlspecialchars($name) ?></b></span>
                <a href="../core/user_handle.php?action=logout"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow">
                    Logout
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8 space-y-6">
        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left: Courses -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-cardBg p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h2 class="text-lg font-semibold text-accent mb-4">Your Courses</h2>
                    <?php if ($courses): ?>
                        <div class="space-y-4">
                            <?php foreach ($courses as $course): ?>
                                <div class="p-4 bg-lightBg rounded-lg border border-gray-200 shadow-sm">
                                    <div class="mb-3">
                                        <div class="font-medium text-textDark"><?= htmlspecialchars($course['course_name']) ?></div>
                                        <div class="text-sm text-textMuted"><?= htmlspecialchars($course['year_level']) ?></div>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="check_in">
                                            <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                                            <button type="submit"
                                                class="w-full px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md text-sm">
                                                Check In
                                            </button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="check_out">
                                            <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                                            <button type="submit"
                                                class="w-full px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md text-sm">
                                                Check Out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-textMuted">You are not enrolled in any courses yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-cardBg p-6 rounded-2xl shadow-lg border border-gray-200 mt-6">
                <h2 class="text-lg font-semibold text-accent mb-4">Submit Excuse Letter</h2>
                <form method="POST" enctype="multipart/form-data">
                    <label class="block mb-2">Select Course</label>
                    <select name="course_id" class="w-full border rounded-lg p-2 mb-4" required>
                        <option value="">-- Choose Course --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= (int) $course['id'] ?>">
                                <?= htmlspecialchars($course['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label class="block mb-2">Reason</label>
                    <textarea name="reason" class="w-full border rounded-lg p-2 mb-4" required></textarea>

                    <label class="block mb-2">Upload File (Optional)</label>
                    <input type="file" name="file" class="w-full border rounded-lg p-2 mb-4">

                    <button type="submit" name="submit_excuse"
                        class="px-4 py-2 bg-accent hover:bg-blue-600 text-white rounded-lg shadow">
                        Submit Excuse Letter
                    </button>
                </form>
            </div>

            <!-- Right: Attendance History -->
            <div class="lg:col-span-2">
                <div class="bg-cardBg p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h2 class="text-lg font-semibold text-accent mb-4">Attendance History</h2>
                    <?php if ($history): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border border-gray-200 rounded-lg overflow-hidden">
                                <thead class="bg-lightBg text-accent">
                                    <tr>
                                        <th class="p-3">Date</th>
                                        <th class="p-3">Course</th>
                                        <th class="p-3">Year</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3">Check-in</th>
                                        <th class="p-3">Check-out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $row): ?>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="p-3"><?= htmlspecialchars($row['attendance_date']) ?></td>
                                            <td class="p-3"><?= htmlspecialchars($row['course_name']) ?></td>
                                            <td class="p-3"><?= htmlspecialchars($row['year_level'] ?? '') ?></td>
                                            <td class="p-3 font-semibold">
                                                <span
                                                    class="<?= ($row['status'] === 'Present') ? 'text-green-600' : (($row['status'] === 'Late') ? 'text-yellow-600' : 'text-red-600') ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                            <td class="p-3"><?= htmlspecialchars($row['check_in'] ?? '-') ?></td>
                                            <td class="p-3"><?= htmlspecialchars($row['check_out'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-textMuted">No attendance records yet.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</body>

</html>
