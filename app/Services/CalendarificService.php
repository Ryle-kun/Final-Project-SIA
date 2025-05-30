<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class CalendarificService
{
    protected $apiKey;
    protected $baseUrl = 'https://calendarific.com/api/v2';

    public function __construct()
    {
        $this->apiKey = config('services.calendarific.api_key');
    }

    public function getHolidays($year = null, $month = null, $country = 'US')
    {
        $year = $year ?? Carbon::now()->year;
        $params = [
            'api_key' => $this->apiKey,
            'country' => $country,
            'year' => $year,
        ];

        if ($month) {
            $params['month'] = $month;
        }

        $response = Http::get("{$this->baseUrl}/holidays", $params);

        return $response->json();
    }

    public function getMonthHolidays($country = 'US')
    {
        $now = Carbon::now();
        return $this->getHolidays($now->year, $now->month, $country);
    }

    public function getYearHolidays($year = null, $country = 'US')
    {
        $year = $year ?? Carbon::now()->year;
        return $this->getHolidays($year, null, $country);
    }
} 