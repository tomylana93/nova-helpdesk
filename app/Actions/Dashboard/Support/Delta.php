<?php

namespace App\Actions\Dashboard\Support;

class Delta
{
    /**
     * @return array{deltaPercent: int|null, direction: 'up'|'down'|'flat'}
     */
    public static function compute(int $current, int $previous): array
    {
        if ($current === $previous) {
            return ['deltaPercent' => 0, 'direction' => 'flat'];
        }

        $direction = $current > $previous ? 'up' : 'down';

        if ($previous === 0) {
            return ['deltaPercent' => null, 'direction' => $direction];
        }

        $percent = (int) round((($current - $previous) / $previous) * 100);

        return ['deltaPercent' => $percent, 'direction' => $direction];
    }
}
