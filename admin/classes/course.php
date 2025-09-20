<?php
require_once __DIR__ . '/../../student/classes/database.php';

class Course extends Database
{
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

    public function deleteCourse($id)
    {
        $sql = "DELETE FROM courses WHERE id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }

    // ✅ Get attendance by course + year level
    public function getAttendanceByCourse($course_id, $year_level)
    {
        $sql = "SELECT a.id, u.name AS student_name, c.course_name, c.year_level,
                       a.attendance_date, a.status, a.check_in, a.check_out
                FROM attendance a
                JOIN users u ON a.student_id = u.id
                JOIN courses c ON a.course_id = c.id
                WHERE c.id = ? AND c.year_level = ?
                ORDER BY a.attendance_date DESC";
        return $this->executeQuery($sql, [$course_id, $year_level]);
    }
}