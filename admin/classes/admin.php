<?php
require_once __DIR__ . '/../../student/classes/Database.php';
require_once __DIR__ . '/../../student/classes/User.php';

class Admin extends User
{
    /* ---------- USER MANAGEMENT ---------- */
    public function getAllStudents()
    {
        $sql = "SELECT id, name, email 
                FROM users
                WHERE role = 'student'";
        return $this->executeQuery($sql);
    }

    public function getAllAdmins()
    {
        $sql = "SELECT id, name, email 
                FROM users
                WHERE role = 'admin'";
        return $this->executeQuery($sql);
    }

    public function makeAdmin($userId)
    {
        $sql = "UPDATE users SET role = 'admin' WHERE id = ?";
        return $this->executeNonQuery($sql, [$userId]);
    }

    public function revokeAdmin($userId)
    {
        $sql = "UPDATE users SET role = 'student' WHERE id = ?";
        return $this->executeNonQuery($sql, [$userId]);
    }

    public function deleteUserAccount($userId)
    {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->executeNonQuery($sql, [$userId]);
    }

    /* ---------- COURSE MANAGEMENT ---------- */
    public function addCourse($course_name, $year_level)
    {
        $sql = "INSERT INTO courses (course_name, year_level) VALUES (?, ?)";
        return $this->executeNonQuery($sql, [$course_name, $year_level]);
    }

    public function getAllCourses()
    {
        $sql = "SELECT * FROM courses ORDER BY year_level, course_name";
        return $this->executeQuery($sql);
    }

    public function updateCourse($id, $course_name, $year_level)
    {
        $sql = "UPDATE courses 
                SET course_name = ?, year_level = ? 
                WHERE id = ?";
        return $this->executeNonQuery($sql, [$course_name, $year_level, $id]);
    }

    public function deleteCourse($id)
    {
        $sql = "DELETE FROM courses WHERE id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }

    public function getAttendanceByYearLevel($year_level)
    {
        $sql = "SELECT a.attendance_date, a.status, a.is_late, 
                       u.name AS student_name, u.email, 
                       c.course_name, c.year_level
                FROM attendance a
                JOIN users u ON a.student_id = u.id
                JOIN courses c ON a.course_id = c.id
                WHERE c.year_level = ?
                ORDER BY a.attendance_date DESC";
        return $this->executeQuery($sql, [$year_level]);
    }

    public function enrollStudent($student_id, $course_id)
    {
        $sql = "INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)";
        return $this->executeNonQuery($sql, [$student_id, $course_id]);
    }

    public function unenrollStudent($student_id, $course_id)
    {
        $sql = "DELETE FROM enrollments WHERE student_id = ? AND course_id = ?";
        return $this->executeNonQuery($sql, [$student_id, $course_id]);
    }

    public function getEnrollments()
    {
        $sql = "SELECT e.student_id, u.name AS student_name, e.course_id, c.course_name, c.year_level
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            JOIN courses c ON e.course_id = c.id
            ORDER BY c.year_level, c.course_name";
        return $this->executeQuery($sql);
    }
    public function getAttendanceByCourse($courseId, $yearLevel = null)
    {
        $sql = "SELECT a.attendance_date, a.status, a.check_in, a.check_out,
                       u.name AS student_name, c.course_name, c.year_level
                FROM attendance a
                JOIN users u ON a.student_id = u.id
                JOIN courses c ON a.course_id = c.id
                WHERE a.course_id = ?";

        $params = [$courseId];

        if ($yearLevel) {
            $sql .= " AND c.year_level = ?";
            $params[] = $yearLevel;
        }

        $sql .= " ORDER BY a.attendance_date DESC";

        return $this->executeQuery($sql, $params);
    }

    /**
     * Get attendance summary grouped by course & year level
     */
    public function getAttendanceSummary()
    {
        $sql = "SELECT c.course_name, c.year_level, 
                       COUNT(a.id) AS total_records,
                       SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS total_present,
                       SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS total_absent,
                       SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) AS total_late,
                       SUM(CASE WHEN a.status = 'Excused' THEN 1 ELSE 0 END) AS total_excused
                FROM courses c
                LEFT JOIN attendance a ON c.id = a.course_id
                GROUP BY c.id, c.course_name, c.year_level
                ORDER BY c.year_level, c.course_name";
        return $this->executeQuery($sql);
    }

    public function getPendingExcuseLetters() {
        $sql = "SELECT el.id, el.reason, el.file_path, el.status, el.submitted_at,
                    u.name AS student_name, c.course_name, c.year_level
                FROM excuse_letters el
                JOIN users u ON el.student_id = u.id
                JOIN courses c ON el.course_id = c.id
                ORDER BY el.submitted_at DESC";
        return $this->executeQuery($sql);
    }

    public function updateExcuseLetterStatus($id, $status) {
        $sql = "UPDATE excuse_letters SET status = ? WHERE id = ?";
        return $this->executeNonQuery($sql, [$status, $id]);
    }

}