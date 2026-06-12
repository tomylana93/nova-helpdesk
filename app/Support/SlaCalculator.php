<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

class SlaCalculator
{
    public function __construct(
        private readonly IndonesiaCalendar $calendar
    ) {}

    /**
     * Add working minutes to a given start date, excluding break hours (12:00 - 13:00),
     * weekends (Saturday and Sunday), and Indonesian national holidays / cuti bersama.
     */
    public function addWorkingMinutes(CarbonInterface $start, int $minutes): CarbonInterface
    {
        if ($minutes <= 0) {
            return Date::instance($start)->copy();
        }

        $time = Date::instance($start)->copy();
        $secondsRemaining = $minutes * 60;

        while ($secondsRemaining > 0) {
            // 1. If it's a holiday or weekend, advance to 09:00:00 of the next day
            if ($this->calendar->isHolidayOrWeekend($time)) {
                $time = $time->addDay()->setTime(9, 0, 0);

                continue;
            }

            // 2. Adjust time if it falls outside or at boundaries of working segments
            $hour = $time->hour;

            // Before 09:00:00
            if ($hour < 9) {
                $time = $time->setTime(9, 0, 0);

                continue;
            }

            // Break hour (12:00:00 - 13:00:00)
            if ($hour === 12) {
                $time = $time->setTime(13, 0, 0);

                continue;
            }

            // After 18:00:00
            if ($hour >= 18) {
                $time = $time->addDay()->setTime(9, 0, 0);

                continue;
            }

            // 3. Now we are inside either:
            // Segment 1: [09:00:00, 12:00:00)
            // Segment 2: [13:00:00, 18:00:00)
            if ($hour < 12) {
                // Segment 1
                $targetLimit = $time->copy()->setTime(12, 0, 0);
                $avail = $time->diffInSeconds($targetLimit);

                if ($secondsRemaining <= $avail) {
                    $time = $time->addSeconds($secondsRemaining);
                    $secondsRemaining = 0;
                } else {
                    $secondsRemaining -= $avail;
                    $time = $time->setTime(13, 0, 0);
                }
            } else {
                // Segment 2
                $targetLimit = $time->copy()->setTime(18, 0, 0);
                $avail = $time->diffInSeconds($targetLimit);

                if ($secondsRemaining <= $avail) {
                    $time = $time->addSeconds($secondsRemaining);
                    $secondsRemaining = 0;
                } else {
                    $secondsRemaining -= $avail;
                    $time = $time->addDay()->setTime(9, 0, 0);
                }
            }
        }

        return $time;
    }
}
