<?php

class ResumeAnalysisLoader
{
    /**
     * Loads a resume + its latest analysis, checking the viewer is allowed to see it.
     * Returns null if not found or not permitted.
     */
    public static function load(int $resumeId, int $viewerId, bool $viewerIsAdmin): ?array
    {
        $db = Database::connection();

        $stmt = $db->prepare('SELECT * FROM resumes WHERE id = ?');
        $stmt->execute([$resumeId]);
        $resume = $stmt->fetch();

        if (!$resume || ($resume['user_id'] != $viewerId && !$viewerIsAdmin)) {
            return null;
        }

        $stmt = $db->prepare('SELECT * FROM resume_analysis WHERE resume_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$resumeId]);
        $analysis = $stmt->fetch();

        if (!$analysis) {
            return null;
        }

        $decode = fn ($json) => json_decode($json ?? '[]', true) ?: [];

        return [
            'resume' => $resume,
            'analysis' => $analysis,
            'education' => $decode($analysis['education']),
            'skills' => $decode($analysis['skills']),
            'technicalSkills' => $decode($analysis['technical_skills']),
            'softSkills' => $decode($analysis['soft_skills']),
            'experience' => $decode($analysis['experience']),
            'projects' => $decode($analysis['projects']),
            'certifications' => $decode($analysis['certifications']),
            'achievements' => $decode($analysis['achievements']),
            'strengths' => $decode($analysis['strengths']),
            'weaknesses' => $decode($analysis['weaknesses']),
            'missingSkills' => $decode($analysis['missing_skills']),
            'suggestions' => $decode($analysis['suggestions']),
            'recommendedRoles' => $decode($analysis['recommended_roles']),
        ];
    }
}

function scoreClass(int $score): string
{
    if ($score >= 75) return 'good';
    if ($score >= 50) return 'ok';
    return 'poor';
}
