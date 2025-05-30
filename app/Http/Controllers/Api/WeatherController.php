<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
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

    public function getCurrentWeather(Request $request)
    {
        try {
            $city = $request->input('city');
            if (!$city) {
                Log::error('Weather API: City parameter is missing');
                return response()->json(['error' => 'City parameter is required'], 400);
            }

            $weather = $this->weatherService->getCurrentWeather($city);
            if (!$weather) {
                Log::error('Weather API: Could not fetch weather data for city: ' . $city);
                return response()->json(['error' => 'Could not fetch weather data'], 500);
            }

            return response()->json($weather);
        } catch (\Exception $e) {
            Log::error('Weather API Error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching weather data'], 500);
        }
    }

    public function getForecast(Request $request)
    {
        try {
            $city = $request->input('city');
            if (!$city) {
                Log::error('Weather API: City parameter is missing for forecast');
                return response()->json(['error' => 'City parameter is required'], 400);
            }

            $forecast = $this->weatherService->getForecast($city);
            if (!$forecast) {
                Log::error('Weather API: Could not fetch forecast data for city: ' . $city);
                return response()->json(['error' => 'Could not fetch forecast data'], 500);
            }

            return response()->json($forecast);
        } catch (\Exception $e) {
            Log::error('Weather API Error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching forecast data'], 500);
        }
    }

    public function getWeatherByCoordinates(Request $request)
    {
        try {
            $lat = $request->input('lat');
            $lon = $request->input('lon');

            if (!$lat || !$lon) {
                Log::error('Weather API: Coordinates are missing');
                return response()->json(['error' => 'Latitude and longitude are required'], 400);
            }

            $weather = $this->weatherService->getWeatherByCoordinates($lat, $lon);
            if (!$weather) {
                Log::error('Weather API: Could not fetch weather data for coordinates: ' . $lat . ', ' . $lon);
                return response()->json(['error' => 'Could not fetch weather data'], 500);
            }

            return response()->json($weather);
        } catch (\Exception $e) {
            Log::error('Weather API Error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching weather data'], 500);
        }
    }
}
