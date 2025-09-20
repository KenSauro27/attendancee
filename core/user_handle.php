<?php
require_once __DIR__ . '/../student/classes/user.php';
$user = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['action'] === 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($user->loginUser($email, $password)) {
            if ($_SESSION['role'] === 'student') {
                header("Location: ../student/student_dashboard.php");
            } else {
                header("Location: ../admin/admin_dashboard.php");
            }
            exit;
        } else {
            $_SESSION['error'] = "Invalid login!";
            header("Location: ../login.php");
            exit;

        }
    }


    if ($_POST['action'] === 'register') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        if ($user->registerUser($name, $email, $password, $role)) {
            $_SESSION['success'] = "Registered successfully! Please log in.";
        } else {
            $_SESSION['error'] = "Registration failed!";
        }
        header("Location: ../login.php");
        exit;
    }
}

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $user->logout();
    header("Location: ../login.php");
    exit;
}