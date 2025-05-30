<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TimezoneService;
use Illuminate\Http\Request;

class TimezoneController extends Controller
{
    protected $timezoneService;

    public function __construct(TimezoneService $timezoneService)
    {
        $this->timezoneService = $timezoneService;
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

    public function getTimezoneByCity(Request $request)
    {
        $request->validate([
            'city' => 'required|string',
        ]);

        $timezone = $this->timezoneService->getTimezoneByCity($request->city);
        return response()->json($timezone);
    }

    public function getTimezoneByCoordinates(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $timezone = $this->timezoneService->getTimezoneByCoordinates(
            $request->lat,
            $request->lng
        );
        return response()->json($timezone);
    }

    public function listTimezones()
    {
        $timezones = $this->timezoneService->listTimezones();
        return response()->json($timezones);
    }
}
