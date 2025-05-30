<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalendarificService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    protected $calendarificService;

    public function __construct(CalendarificService $calendarificService)
    {
        $this->calendarificService = $calendarificService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getMonthHolidays(Request $request)
    {
        $request->validate([
            'country' => 'nullable|string|size:2',
        ]);

        $holidays = $this->calendarificService->getMonthHolidays(
            $request->input('country', 'US')
        );
        return response()->json($holidays);
    }

    public function getYearHolidays(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:1900|max:2100',
            'country' => 'nullable|string|size:2',
        ]);

        $holidays = $this->calendarificService->getYearHolidays(
            $request->year,
            $request->input('country', 'US')
        );
        return response()->json($holidays);
    }

    public function getHolidays(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:1900|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'country' => 'nullable|string|size:2',
        ]);

        $holidays = $this->calendarificService->getHolidays(
            $request->year,
            $request->month,
            $request->input('country', 'US')
        );
        return response()->json($holidays);
    }
}
