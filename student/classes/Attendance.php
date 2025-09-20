<?php
require_once 'Database.php';

class Attendance extends Database
{
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

    public function getAttendanceHistory($studentId)
    {
        $sql = "SELECT a.id, a.attendance_date, a.status, a.check_in, a.check_out, 
                       c.course_name, c.year_level
                FROM attendance a
                JOIN courses c ON a.course_id = c.id
                WHERE a.student_id = ?
                ORDER BY a.attendance_date DESC";
        return $this->executeQuery($sql, [$studentId]);
    }

    public function submitExcuseLetter($studentId, $courseId, $reason, $filePath = null) {
        $sql = "INSERT INTO excuse_letters (student_id, course_id, reason, file_path) 
                VALUES (?, ?, ?, ?)";
        return $this->executeNonQuery($sql, [$studentId, $courseId, $reason, $filePath]);
    }
}