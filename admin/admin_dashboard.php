<?php
session_start();
require_once __DIR__ . '/classes/admin.php';

$admin = new Admin();
$admin->startSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_excuse') {
        $admin->updateExcuseLetterStatus($_POST['id'], $_POST['status']);
        header("Location: admin_dashboard.php");
        exit;
    }
}

// Handle course form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_course') {
        $admin->addCourse($_POST['course_name'], $_POST['year_level']);
        header("Location: admin_dashboard.php");
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'edit_course') {
        $admin->updateCourse($_POST['id'], $_POST['course_name'], $_POST['year_level']);
        header("Location: admin_dashboard.php");
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'enroll_student') {
        $admin->enrollStudent($_POST['student_id'], $_POST['course_id']);
        header("Location: admin_dashboard.php");
        exit;
    }
}

// Delete course via GET
if (isset($_GET['action']) && $_GET['action'] === 'delete_course') {
    $admin->deleteCourse($_GET['id']);
    header("Location: admin_dashboard.php");
    exit;
}

// Unenroll student
if (isset($_GET['action']) && $_GET['action'] === 'unenroll_student') {
    $admin->unenrollStudent($_GET['student_id'], $_GET['course_id']);
    header("Location: admin_dashboard.php");
    exit;
}

// Load data
$students = $admin->getAllStudents();
$courses = $admin->getAllCourses();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          colors: {
            accent: '#2563EB',
            muted: '#6B7280',
            bg: '#F3F4F6',
            card: '#FFFFFF'
          }
        }
      }
    }
  </script>
</head>
<body class="bg-bg text-gray-900 min-h-screen flex flex-col">

  <!-- TOP NAV -->
  <header class="bg-card shadow px-8 py-4 flex items-center justify-between border-b border-gray-200">
    <h1 class="text-xl font-bold text-accent">Admin Dashboard</h1>
    <div class="flex items-center gap-4">
      <span class="text-sm text-muted">
        Welcome, <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></strong>
      </span>
      <a href="../core/user_handle.php?action=logout"
         class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm">
        Logout
      </a>
    </div>
  </header>

  <!-- MAIN -->
  <main class="flex-1 p-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Course Management (spans 2 cols on large screens) -->
    <section id="courses" class="bg-card rounded-xl shadow p-6 border border-gray-200 lg:col-span-2">
      <h2 class="text-lg font-semibold text-accent mb-4">📘 Course Management</h2>

      <form method="POST" class="grid md:grid-cols-3 gap-3 mb-6">
        <input type="hidden" name="action" value="add_course">
        <input type="text" name="course_name" placeholder="Course Name" required
               class="px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-accent">
        <input type="text" name="year_level" placeholder="Year Level" required
               class="px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-accent">
        <button type="submit"
                class="px-3 py-2 rounded-md bg-accent text-white font-medium hover:bg-blue-600">
          ➕ Add Course
        </button>
      </form>

      <div class="overflow-x-auto">
        <table class="w-full text-left border border-gray-200 rounded-lg overflow-hidden">
          <thead class="bg-gray-50 text-accent">
            <tr>
              <th class="p-3">ID</th>
              <th class="p-3">Course</th>
              <th class="p-3">Year</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($courses) && is_array($courses)): ?>
              <?php foreach ($courses as $c): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                  <td class="p-3"><?= (int)$c['id'] ?></td>
                  <td class="p-3"><?= htmlspecialchars($c['course_name']) ?></td>
                  <td class="p-3"><?= htmlspecialchars($c['year_level']) ?></td>
                  <td class="p-3 space-x-2">
                    <!-- EDIT: type=button + data-* attributes (safe-escaped) -->
                    <button type="button"
                      class="edit-btn px-3 py-1 rounded-md bg-yellow-400 hover:bg-yellow-500 text-sm"
                      data-id="<?= (int)$c['id'] ?>"
                      data-name="<?= htmlspecialchars($c['course_name'], ENT_QUOTES) ?>"
                      data-year="<?= htmlspecialchars($c['year_level'], ENT_QUOTES) ?>">
                      Edit
                    </button>

                    <a href="?action=delete_course&id=<?= (int)$c['id'] ?>"
                       class="px-3 py-1 rounded-md bg-red-500 hover:bg-red-600 text-white text-sm"
                       onclick="return confirm('Delete this course?')">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="4" class="p-3 text-muted">No courses yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Enrollment -->
    <section id="enrollment" class="bg-card rounded-xl shadow p-6 border border-gray-200">
      <h2 class="text-lg font-semibold text-accent mb-4">👩‍🎓 Enrollment</h2>

      <form method="POST" class="space-y-3 mb-6">
        <input type="hidden" name="action" value="enroll_student">
        <select name="student_id" required
          class="w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-accent">
          <option value="">Select Student</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <select name="course_id" required
          class="w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-accent">
          <option value="">Select Course</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?> (<?= htmlspecialchars($c['year_level']) ?>)</option>
          <?php endforeach; ?>
        </select>

        <button type="submit" class="w-full py-2 rounded-md bg-accent text-white font-medium hover:bg-blue-600">
          ✅ Enroll
        </button>
      </form>

      <div class="overflow-x-auto">
        <table class="w-full text-left border border-gray-200 rounded-lg overflow-hidden">
          <thead class="bg-gray-50 text-accent">
            <tr>
              <th class="p-3">Student</th>
              <th class="p-3">Course</th>
              <th class="p-3">Year</th>
              <th class="p-3">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($admin->getEnrollments() as $e): ?>
              <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="p-3"><?= htmlspecialchars($e['student_name']) ?></td>
                <td class="p-3"><?= htmlspecialchars($e['course_name']) ?></td>
                <td class="p-3"><?= htmlspecialchars($e['year_level']) ?></td>
                <td class="p-3">
                  <a href="?action=unenroll_student&student_id=<?= (int)$e['student_id'] ?>&course_id=<?= (int)$e['course_id'] ?>"
                     onclick="return confirm('Unenroll this student?')"
                     class="px-3 py-1 rounded-md bg-red-500 text-white hover:bg-red-600 text-sm">Unenroll</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Attendance Reports (full width row) -->
    <section id="attendance" class="bg-card rounded-xl shadow p-6 border border-gray-200 lg:col-span-3">
      <h2 class="text-lg font-semibold text-accent mb-4">📊 Attendance Reports</h2>
      <?php $summary = $admin->getAttendanceSummary(); ?>
      <?php if ($summary): ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-50 text-accent">
              <tr>
                <th class="p-3">Course</th>
                <th class="p-3">Year</th>
                <th class="p-3">Total</th>
                <th class="p-3">Present</th>
                <th class="p-3">Absent</th>
                <th class="p-3">Late</th>
                <th class="p-3">Excused</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($summary as $row): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                  <td class="p-3"><?= htmlspecialchars($row['course_name']) ?></td>
                  <td class="p-3"><?= htmlspecialchars($row['year_level']) ?></td>
                  <td class="p-3"><?= (int)$row['total_records'] ?></td>
                  <td class="p-3 text-green-600"><?= (int)$row['total_present'] ?></td>
                  <td class="p-3 text-red-600"><?= (int)$row['total_absent'] ?></td>
                  <td class="p-3 text-yellow-600"><?= (int)$row['total_late'] ?></td>
                  <td class="p-3 text-blue-600"><?= (int)$row['total_excused'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted">No attendance records available.</p>
      <?php endif; ?>
    </section>


    <!-- Excuse Letters -->
      <section id="excuse-letters" class="bg-card rounded-xl shadow p-6 border border-gray-200 lg:col-span-3">
        <h2 class="text-lg font-semibold text-accent mb-4">📄 Excuse Letters</h2>

        <?php $excuses = $admin->getPendingExcuseLetters(); ?>
        <?php if ($excuses): ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left border border-gray-200 rounded-lg overflow-hidden">
              <thead class="bg-gray-50 text-accent">
                <tr>
                  <th class="p-3">Student</th>
                  <th class="p-3">Course</th>
                  <th class="p-3">Reason</th>
                  <th class="p-3">File</th>
                  <th class="p-3">Status</th>
                  <th class="p-3">Submitted</th>
                  <th class="p-3">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($excuses as $ex): ?>
                  <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-3"><?= htmlspecialchars($ex['student_name']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($ex['course_name']) ?> (<?= htmlspecialchars($ex['year_level']) ?>)</td>
                    <td class="p-3"><?= htmlspecialchars($ex['reason']) ?></td>
                    <td class="p-3">
                      <?php if (!empty($ex['file_path'])): ?>
                        <a href="../uploads/<?= htmlspecialchars($ex['file_path']) ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
                      <?php else: ?>
                        -
                      <?php endif; ?>
                    </td>
                    <td class="p-3 font-semibold 
                              <?= $ex['status'] === 'Approved' ? 'text-green-600' : ($ex['status'] === 'Rejected' ? 'text-red-600' : 'text-yellow-600') ?>">
                      <?= htmlspecialchars($ex['status']) ?>
                    </td>
                    <td class="p-3"><?= htmlspecialchars($ex['submitted_at']) ?></td>
                    <td class="p-3">
                      <?php if ($ex['status'] === 'Pending'): ?>
                        <form method="POST" class="flex gap-2">
                          <input type="hidden" name="action" value="update_excuse">
                          <input type="hidden" name="id" value="<?= (int)$ex['id'] ?>">
                          <button name="status" value="Approved"
                            class="px-3 py-1 rounded-md bg-green-500 text-white hover:bg-green-600 text-sm">Approve</button>
                          <button name="status" value="Rejected"
                            class="px-3 py-1 rounded-md bg-red-500 text-white hover:bg-red-600 text-sm">Reject</button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted">No action</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted">No excuse letters submitted yet.</p>
        <?php endif; ?>
      </section>

  </main>

  <!-- Edit Modal -->
  <div id="editForm" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50">
    <div class="bg-card p-6 rounded-xl shadow-2xl w-96 border border-gray-200">
      <h3 class="text-lg font-semibold text-accent mb-4">Edit Course</h3>
      <form method="POST">
        <input type="hidden" name="action" value="edit_course">
        <input type="hidden" name="id" id="editId">
        <input type="text" name="course_name" id="editCourseName" required
               class="w-full mb-3 px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-accent">
        <input type="text" name="year_level" id="editYearLevel" required
               class="w-full mb-3 px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-accent">
        <div class="flex justify-end gap-2">
          <button type="button" onclick="closeEditForm()" class="px-3 py-2 rounded-md bg-gray-100 hover:bg-gray-200">Cancel</button>
          <button type="submit" class="px-3 py-2 rounded-md bg-accent text-white hover:bg-blue-600">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Helper functions for modal open/close
    function openEditForm(id, name, year) {
      document.getElementById('editId').value = id;
      document.getElementById('editCourseName').value = name;
      document.getElementById('editYearLevel').value = year;
      const modal = document.getElementById('editForm');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }
    function closeEditForm() {
      const modal = document.getElementById('editForm');
      modal.classList.remove('flex');
      modal.classList.add('hidden');
    }

    // Attach handlers to all .edit-btn elements (works reliably)
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function () {
          const id = btn.dataset.id;
          const name = btn.dataset.name;
          const year = btn.dataset.year;
          openEditForm(id, name, year);
        });
      });

      // Optional: close modal when clicking outside modal content
      document.getElementById('editForm').addEventListener('click', function (e) {
        if (e.target === this) closeEditForm();
      });

      // Optional: close on ESC
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          const modal = document.getElementById('editForm');
          if (!modal.classList.contains('hidden')) closeEditForm();
        }
      });
    });
  </script>
</body>
</html>
