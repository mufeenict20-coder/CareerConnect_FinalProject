<?php
class MatchingEngine {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function calculateMatchScore(int $studentId, int $jobId): array {
        // Fetch Student Skills
        $stmtStudent = $this->db->prepare("
            SELECT s.skill_id, s.skill_name FROM student_skills ss
            JOIN skills s ON ss.skill_id = s.skill_id
            WHERE ss.student_id = :student_id
        ");
        $stmtStudent->execute([':student_id' => $studentId]);
        $studentSkills = $stmtStudent->fetchAll(PDO::FETCH_KEY_PAIR);

        // Fetch Required Job Skills
        $stmtJob = $this->db->prepare("
            SELECT s.skill_id, s.skill_name FROM job_skills js
            JOIN skills s ON js.skill_id = s.skill_id
            WHERE js.job_id = :job_id
        ");
        $stmtJob->execute([':job_id' => $jobId]);
        $jobSkills = $stmtJob->fetchAll(PDO::FETCH_KEY_PAIR);

        $totalRequired = count($jobSkills);
        if ($totalRequired === 0) {
            return ['score' => 100.0, 'matching_skills' => [], 'missing_skills' => []];
        }

        $matchingSkills = array_intersect_key($studentSkills, $jobSkills);
        $missingSkills  = array_diff_key($jobSkills, $studentSkills);

        $score = round((count($matchingSkills) / $totalRequired) * 100, 1);

        return [
            'score'           => $score,
            'matching_skills' => array_values($matchingSkills),
            'missing_skills'  => array_values($missingSkills)
        ];
    }
}