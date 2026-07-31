<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    session_start();
}

class Auth {
    public static function login(PDO $db, string $email, string $password): bool {
        $stmt = $db->prepare("
            SELECT u.user_id, u.name, u.email, u.password, u.role, 
                   s.student_id, e.employer_id, e.is_verified
            FROM users u
            LEFT JOIN students s ON u.user_id = s.user_id
            LEFT JOIN employers e ON u.user_id = e.user_id
            WHERE u.email = :email
            LIMIT 1
        ");
        
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']     = (int)$user['user_id'];
            $_SESSION['name']        = $user['name'];
            $_SESSION['email']       = $user['email'];
            $_SESSION['role']        = $user['role'];
            $_SESSION['student_id']  = $user['student_id'] ? (int)$user['student_id'] : null;
            $_SESSION['employer_id'] = $user['employer_id'] ? (int)$user['employer_id'] : null;
            $_SESSION['is_verified'] = (int)($user['is_verified'] ?? 0);
            return true;
        }
        return false;
    }

    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function requireRole(array $allowedRoles): void {
        if (!self::check()) {
            header("Location: login.php");
            exit;
        }
        if (!in_array($_SESSION['role'], $allowedRoles, true)) {
            header("Location: index.php?error=unauthorized");
            exit;
        }
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }
}