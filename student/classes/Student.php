<?php
require_once 'Database.php';

class Student extends Database
{
    public function getStudentById($id)
    {
        $sql = "SELECT id, name, email, role 
                FROM users 
                WHERE id = ? AND role = 'student'";
        return $this->executeQuerySingle($sql, [$id]);
    }

    public function getAllStudents()
    {
        $sql = "SELECT id, name, email 
                FROM users 
                WHERE role = 'student'";
        return $this->executeQuery($sql);
    }

    public function getEnrolledCourses($studentId)
    {
        $sql = "SELECT c.id, c.course_name, c.year_level
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.student_id = ?";
        return $this->executeQuery($sql, [$studentId]);
    }

    public function getAttendanceHistory($studentId)
    {
        $sql = "SELECT a.attendance_date, a.status, a.is_late, c.course_name, c.year_level
                FROM attendance a
                JOIN courses c ON a.course_id = c.id
                WHERE a.student_id = ?
                ORDER BY a.attendance_date DESC";
        return $this->executeQuery($sql, [$studentId]);
    }

    public function logAttendance($studentId, $courseId, $status = 'Present', $checkIn = null, $checkOut = null)
    {
        $sql = "INSERT INTO attendance (student_id, course_id, attendance_date, status, check_in, check_out) 
            VALUES (?, ?, CURDATE(), ?, ?, ?)";
        return $this->executeNonQuery($sql, [$studentId, $courseId, $status, $checkIn, $checkOut]);
    }

    public function updateAttendance($attendanceId, $status, $checkOut = null)
    {
        $sql = "UPDATE attendance 
            SET status = ?, check_out = ? 
            WHERE id = ?";
        return $this->executeNonQuery($sql, [$status, $checkOut, $attendanceId]);
    }

    public function getAttendanceByDate($studentId, $courseId, $date)
    {
        $sql = "SELECT * FROM attendance 
            WHERE student_id = ? AND course_id = ? AND attendance_date = ?";
        return $this->executeQuerySingle($sql, [$studentId, $courseId, $date]);
    }


}