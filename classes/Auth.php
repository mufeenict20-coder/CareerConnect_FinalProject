<?php
// classes/Auth.php

class Auth {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Authenticates user, verifies password hash, and initializes PHP session.
     */
    public function login(string $email, string $password): bool {
        $email = strtolower(trim($email));

        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // Prevent Session Fixation Attacks
                session_regenerate_id(true);

                // Standard session variables
                $_SESSION['user_id']     = (int)$user['user_id'];
                $_SESSION['name']        = $user['name'];
                $_SESSION['email']       = $user['email'];
                $_SESSION['role']        = $user['role'];
                $_SESSION['profile_pic'] = $user['profile_pic'] ?? null;

                // Fetch role-specific IDs safely
                if ($user['role'] === 'Student') {
                    $sStmt = $this->db->prepare("SELECT student_id FROM students WHERE user_id = :uid");
                    $sStmt->execute([':uid' => $user['user_id']]);
                    $student = $sStmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['student_id'] = $student ? (int)$student['student_id'] : null;
                } elseif ($user['role'] === 'Employer') {
                    $eStmt = $this->db->prepare("SELECT employer_id, is_verified FROM employers WHERE user_id = :uid");
                    $eStmt->execute([':uid' => $user['user_id']]);
                    $employer = $eStmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['employer_id'] = $employer ? (int)$employer['employer_id'] : null;
                    $_SESSION['is_verified'] = $employer ? (int)$employer['is_verified'] : 0;
                }

                return true;
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Clears all session data and destroys the current user session.
     */
    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear all session variables
        $_SESSION = array();

        // Expire the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        // Destroy session instance
        session_destroy();
    }

    /**
     * Strict Role Access Guard
     */
    public static function requireRole(array $allowedRoles): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
            header("Location: login.php");
            exit;
        }
    }
}