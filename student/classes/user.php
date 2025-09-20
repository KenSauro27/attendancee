<?php

require_once 'Database.php';

class User extends Database
{

    /**
     * Starts a new session if one isn't already active.
     */
    public function startSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Checks if the email already exists in the database.
     * @param string $email
     * @return bool
     */
    public function emailExists($email): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $result = $this->executeQuerySingle($sql, [$email]);
        return $result['count'] > 0;
    }

    /**
     * Registers a new user.
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $role ('student' or 'admin')
     * @return bool
     */
    public function registerUser($name, $email, $password, $role = 'student'): bool
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, role, password_hash) VALUES (?, ?, ?, ?)";
        try {
            return $this->executeNonQuery($sql, [$name, $email, $role, $hashed_password]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Logs in a user by verifying credentials.
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function loginUser($email, $password): bool
    {
        $sql = "SELECT id, name, role, password_hash FROM users WHERE email = ?";
        $user = $this->executeQuerySingle($sql, [$email]);

        if ($user && password_verify($password, $user['password_hash'])) {
            $this->startSession();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    /**
     * Checks if a user is currently logged in.
     */
    public function isLoggedIn(): bool
    {
        $this->startSession();
        return isset($_SESSION['user_id']);
    }

    /**
     * Checks if the logged-in user is an admin.
     */
    public function isAdmin(): bool
    {
        $this->startSession();
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    /**
     * Logs out the current user.
     */
    public function logout()
    {
        $this->startSession();
        session_unset();
        session_destroy();
    }

    /**
     * Retrieves users.
     * @param int|null $id
     * @return array|null
     */
    public function getUsers($id = null)
    {
        if ($id) {
            $sql = "SELECT id, name, email, role FROM users WHERE id = ?";
            return $this->executeQuerySingle($sql, [$id]);
        }
        $sql = "SELECT id, name, email, role FROM users";
        return $this->executeQuery($sql);
    }

    /**
     * Updates a user's info.
     * @param int $id
     * @param string $name
     * @param string $email
     * @param string $role
     * @return bool
     */
    public function updateUser($id, $name, $email, $role): bool
    {
        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        return $this->executeNonQuery($sql, [$name, $email, $role, $id]);
    }

    /**
     * Deletes a user.
     * @param int $id
     * @return bool
     */
    public function deleteUser($id): bool
    {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }
}

?>