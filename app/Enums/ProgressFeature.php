<?php

namespace App\Enums;

class ProgressFeature
{
    public const EXPLORE_IN_AR = 'exploreInAR';
    public const GEOGEBRA_LAB = 'geogebraLab';
    public const PROBLEM_CHALLENGE = 'problemChallenge';

    /**
     * @return array<int,string>
     */
    public static function all(): array
    {
        return [
            self::EXPLORE_IN_AR,
            self::GEOGEBRA_LAB,
            self::PROBLEM_CHALLENGE,
        ];
    }
}

