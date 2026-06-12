<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndonesiaCalendar
{
    private const array FALLBACK_2026 = [
        '2026-01-01', // Tahun Baru 2026 Masehi
        '2026-01-02', // Cuti Bersama Tahun Baru
        '2026-01-16', // Isra Mi’raj Nabi Muhammad SAW
        '2026-02-16', // Cuti Bersama Imlek
        '2026-02-17', // Tahun Baru Imlek 2577 Kongzili
        '2026-03-18', // Cuti Bersama Hari Suci Nyepi
        '2026-03-19', // Hari Suci Nyepi
        '2026-03-20', // Cuti Bersama Hari Raya Idul Fitri
        '2026-03-21', // Hari Raya Idul Fitri
        '2026-03-22', // Hari Raya Idul Fitri
        '2026-03-23', // Cuti Bersama Hari Raya Idul Fitri
        '2026-03-24', // Cuti Bersama Hari Raya Idul Fitri
        '2026-04-03', // Wafat Yesus Kristus / Jumat Agung
        '2026-04-05', // Kebangkitan Yesus Kristus (Paskah)
        '2026-05-01', // Hari Buruh Internasional
        '2026-05-14', // Kenaikan Yesus Kristus
        '2026-05-15', // Cuti Bersama Kenaikan Yesus Kristus
        '2026-05-27', // Hari Raya Idul Adha
        '2026-05-28', // Cuti Bersama Hari Raya Idul Adha
        '2026-05-31', // Hari Raya Waisak
        '2026-06-01', // Hari Lahir Pancasila
        '2026-06-16', // Tahun Baru Islam
        '2026-08-17', // Hari Kemerdekaan Republik Indonesia
        '2026-08-25', // Maulid Nabi Muhammad SAW
        '2026-12-24', // Cuti Bersama Hari Raya Natal
        '2026-12-25', // Hari Raya Natal
    ];

    /**
     * Determine if the given date is a weekend, national holiday, or collective leave.
     */
    public function isHolidayOrWeekend(CarbonInterface $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        return $this->isHoliday($date);
    }

    /**
     * Determine if the given date is a national holiday or collective leave.
     */
    public function isHoliday(CarbonInterface $date): bool
    {
        $year = $date->year;
        $dateString = $date->toDateString();

        $holidays = Cache::remember("id_holidays_{$year}", 86400, function () use ($year): array {
            try {
                $response = Http::timeout(3)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/ID");

                if ($response->successful()) {
                    $json = $response->json();
                    if (is_array($json)) {
                        return collect($json)->pluck('date')->all();
                    }
                }
            } catch (Exception $exception) {
                Log::warning("Failed to fetch Indonesia holidays from API for year {$year}: ".$exception->getMessage());
            }

            // Fallback list
            if ($year === 2026) {
                return self::FALLBACK_2026;
            }

            return [];
        });

        return in_array($dateString, $holidays, true);
    }
}
