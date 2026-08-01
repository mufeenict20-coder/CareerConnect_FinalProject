<?php
// classes/User.php

class User {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Registers a new Student user.
     */
    public function registerStudent(string $name, string $email, string $password, string $degree, float $gpa): bool {
        try {
            $this->db->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt1 = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'Student')");
            $stmt1->execute([
                ':name'     => trim($name),
                ':email'    => strtolower(trim($email)),
                ':password' => $hashedPassword
            ]);

            $userId = (int)$this->db->lastInsertId();

            $stmt2 = $this->db->prepare("INSERT INTO students (user_id, degree, gpa) VALUES (:user_id, :degree, :gpa)");
            $stmt2->execute([
                ':user_id' => $userId,
                ':degree'  => trim($degree),
                ':gpa'     => $gpa
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Student Reg Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registers a new Employer user.
     */
    public function registerEmployer(string $name, string $email, string $password, string $companyName, string $industry, ?string $website): bool {
        try {
            $this->db->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt1 = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'Employer')");
            $stmt1->execute([
                ':name'     => trim($name),
                ':email'    => strtolower(trim($email)),
                ':password' => $hashedPassword
            ]);

            $userId = (int)$this->db->lastInsertId();

            $stmt2 = $this->db->prepare("INSERT INTO employers (user_id, company_name, industry, company_website, is_verified) VALUES (:user_id, :company_name, :industry, :website, 0)");
            $stmt2->execute([
                ':user_id'      => $userId,
                ':company_name' => trim($companyName),
                ':industry'     => trim($industry),
                ':website'      => $website ? trim($website) : null
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Employer Reg Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates student profile skills for the Smart Matching Engine.
     */
    public function updateStudentSkills(int $studentId, array $skillIds): void {
        // Delete existing skills for student
        $del = $this->db->prepare("DELETE FROM student_skills WHERE student_id = :student_id");
        $del->execute([':student_id' => $studentId]);

        // Insert newly selected skills
        if (!empty($skillIds)) {
            $ins = $this->db->prepare("INSERT INTO student_skills (student_id, skill_id) VALUES (:student_id, :skill_id)");
            foreach ($skillIds as $skillId) {
                $ins->execute([
                    ':student_id' => $studentId,
                    ':skill_id'   => (int)$skillId
                ]);
            }
        }
    }

    /**
     * Returns all available master skills from the database.
     */
    public function getAllSkills(): array {
        return $this->db->query("SELECT * FROM skills ORDER BY skill_name ASC")->fetchAll();
    }
}