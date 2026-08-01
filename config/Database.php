<?php
class Database {
    private static ?PDO $instance = null;
    private static string $dbPath = __DIR__ . '/../database/careerconnect.sqlite';

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO("sqlite:" . self::$dbPath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Enable foreign keys and WAL mode for SQLite
                self::$instance->exec("PRAGMA foreign_keys = ON;");
                self::$instance->exec("PRAGMA journal_mode = WAL;");
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}