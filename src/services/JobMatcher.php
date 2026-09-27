<?php

class JobMatcher
{
    /**
     * Compares a candidate's skills against a job's required skills.
     * Matching is case-insensitive and trims whitespace.
     *
     * @return array{percentage:int, matched:string[], missing:string[]}
     */
    public static function match(array $candidateSkills, array $requiredSkills): array
    {
        $normalize = fn (string $s) => strtolower(trim($s));

        $candidateSet = array_unique(array_map($normalize, $candidateSkills));
        $requiredSet = array_unique(array_map($normalize, $requiredSkills));

        if (count($requiredSet) === 0) {
            return ['percentage' => 0, 'matched' => [], 'missing' => []];
        }

        $matched = array_values(array_intersect($requiredSet, $candidateSet));
        $missing = array_values(array_diff($requiredSet, $candidateSet));

        $percentage = (int) round((count($matched) / count($requiredSet)) * 100);

        return [
            'percentage' => $percentage,
            'matched' => $matched,
            'missing' => $missing,
        ];
    }
}
