<?php

class GrokException extends Exception {}

class GrokService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->apiKey = $config['grok']['api_key'];
        $this->apiUrl = $config['grok']['api_url'];
        $this->model = $config['grok']['model'];

        if ($this->apiKey === '') {
            throw new GrokException('GROK_API_KEY is not set in .env');
        }
    }

    /**
     * Sends resume text to Grok and returns a fully structured analysis array
     * matching RESUME_ANALYSIS_SCHEMA below.
     */
    public function analyzeResume(string $resumeText): array
    {
        $systemPrompt = <<<PROMPT
You are an expert technical resume reviewer and ATS (Applicant Tracking System) simulator.
Analyze the resume text you are given and extract every field defined by the JSON schema
as accurately as possible. Rules:
- Only extract information that is actually present in the resume text; never invent facts.
- If a field genuinely cannot be found, use null (for single values) or an empty array (for lists).
- resume_score and ats_score are integers from 0 to 100. ats_score should reflect how well the
  resume would survive automated keyword/format screening; resume_score reflects overall quality
  (clarity, impact, structure, achievements).
- strengths, weaknesses, missing_skills and suggestions must be specific and actionable, not generic.
- recommended_roles should be realistic job titles this candidate is currently qualified for.
PROMPT;

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => "Resume text:\n\n" . $resumeText],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'resume_analysis',
                    'strict' => true,
                    'schema' => self::RESUME_ANALYSIS_SCHEMA,
                ],
            ],
            'temperature' => 0.2,
        ];

        $raw = $this->request($payload);
        $parsed = json_decode($raw, true);

        if (!is_array($parsed)) {
            throw new GrokException('Grok returned a response that could not be parsed as JSON.');
        }

        $parsed['_raw_ai_response'] = $raw;
        return $parsed;
    }

    /**
     * Given a resume's extracted skills and a job's required skills, asks Grok
     * to write a short human-readable explanation of the match. The match
     * percentage itself is calculated in PHP (JobMatcher), not by Grok.
     */
    public function explainJobMatch(array $candidateSkills, array $requiredSkills, array $matchedSkills, array $missingSkills, int $matchPercentage): string
    {
        $systemPrompt = 'You are a career advisor. Write a concise (2-4 sentence) explanation of how well a '
            . 'candidate fits a job, given their skills and the job requirements. Be specific about which '
            . 'skills matched and which are missing. Do not repeat the percentage number verbatim in a robotic way.';

        $userContent = json_encode([
            'match_percentage' => $matchPercentage,
            'candidate_skills' => $candidateSkills,
            'required_skills' => $requiredSkills,
            'matched_skills' => $matchedSkills,
            'missing_skills' => $missingSkills,
        ]);

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userContent],
            ],
            'temperature' => 0.4,
        ];

        return trim($this->request($payload));
    }

    private function request(array $payload): string
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new GrokException('Failed to reach Grok API: ' . $curlError);
        }

        $decoded = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $message = $decoded['error']['message'] ?? $response;
            throw new GrokException("Grok API error (HTTP $httpCode): $message");
        }

        return $decoded['choices'][0]['message']['content'] ?? '';
    }

    private const RESUME_ANALYSIS_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'personal_info' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => ['string', 'null']],
                    'email' => ['type' => ['string', 'null']],
                    'phone' => ['type' => ['string', 'null']],
                ],
                'required' => ['name', 'email', 'phone'],
                'additionalProperties' => false,
            ],
            'education' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'degree' => ['type' => 'string'],
                        'institution' => ['type' => 'string'],
                        'year' => ['type' => ['string', 'null']],
                    ],
                    'required' => ['degree', 'institution', 'year'],
                    'additionalProperties' => false,
                ],
            ],
            'skills' => ['type' => 'array', 'items' => ['type' => 'string']],
            'technical_skills' => ['type' => 'array', 'items' => ['type' => 'string']],
            'soft_skills' => ['type' => 'array', 'items' => ['type' => 'string']],
            'experience' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'role' => ['type' => 'string'],
                        'company' => ['type' => 'string'],
                        'duration' => ['type' => ['string', 'null']],
                        'description' => ['type' => ['string', 'null']],
                    ],
                    'required' => ['role', 'company', 'duration', 'description'],
                    'additionalProperties' => false,
                ],
            ],
            'projects' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'description' => ['type' => ['string', 'null']],
                        'technologies' => ['type' => 'array', 'items' => ['type' => 'string']],
                    ],
                    'required' => ['name', 'description', 'technologies'],
                    'additionalProperties' => false,
                ],
            ],
            'certifications' => ['type' => 'array', 'items' => ['type' => 'string']],
            'achievements' => ['type' => 'array', 'items' => ['type' => 'string']],
            'resume_summary' => ['type' => 'string'],
            'resume_score' => ['type' => 'integer'],
            'ats_score' => ['type' => 'integer'],
            'strengths' => ['type' => 'array', 'items' => ['type' => 'string']],
            'weaknesses' => ['type' => 'array', 'items' => ['type' => 'string']],
            'missing_skills' => ['type' => 'array', 'items' => ['type' => 'string']],
            'suggestions' => ['type' => 'array', 'items' => ['type' => 'string']],
            'recommended_roles' => ['type' => 'array', 'items' => ['type' => 'string']],
        ],
        'required' => [
            'personal_info', 'education', 'skills', 'technical_skills', 'soft_skills',
            'experience', 'projects', 'certifications', 'achievements', 'resume_summary',
            'resume_score', 'ats_score', 'strengths', 'weaknesses', 'missing_skills',
            'suggestions', 'recommended_roles',
        ],
        'additionalProperties' => false,
    ];
}
