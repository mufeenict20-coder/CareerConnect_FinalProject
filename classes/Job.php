<?php
class Job {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function createJob(int $employerId, string $title, string $description, string $jobType, string $location, string $salary, string $deadline, array $skillIds): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO jobs (employer_id, title, description, job_type, location, salary_range, deadline)
                VALUES (:employer_id, :title, :description, :job_type, :location, :salary, :deadline)
            ");
            $stmt->execute([
                ':employer_id' => $employerId,
                ':title'       => trim($title),
                ':description' => trim($description),
                ':job_type'    => $jobType,
                ':location'    => trim($location),
                ':salary'      => trim($salary),
                ':deadline'    => $deadline
            ]);

            $jobId = (int)$this->db->lastInsertId();

            $skillStmt = $this->db->prepare("INSERT INTO job_skills (job_id, skill_id) VALUES (:job_id, :skill_id)");
            foreach ($skillIds as $skillId) {
                $skillStmt->execute([
                    ':job_id'  => $jobId,
                    ':skill_id' => (int)$skillId
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Job Creation Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployerJobs(int $employerId): array {
        $stmt = $this->db->prepare("SELECT * FROM jobs WHERE employer_id = :employer_id ORDER BY created_at DESC");
        $stmt->execute([':employer_id' => $employerId]);
        return $stmt->fetchAll();
    }
}