<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TimezoneService
{
    protected $apiKey;
    protected $baseUrl = 'http://api.timezonedb.com/v2.1';

    public function __construct()
    {
        $this->apiKey = config('services.timezonedb.api_key');
    }

    public function getTimezoneByCity($city)
    {
        try {
            Log::info('Starting timezone lookup for: ' . $city);
            
            // First try to match the input against country names to get the country code
            $countryCode = $this->getCountryCode($city);
            
            if ($countryCode) {
                Log::info('Found country code: ' . $countryCode);
                return $this->getTimezoneByCountryCode($countryCode);
            }
            
            // If no country match found, try geocoding the city
            Log::info('No country match found, trying geocoding');
            return $this->geocodeCity($city);
            
        } catch (\Exception $e) {
            Log::error("Timezone lookup error: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => 'Failed to fetch timezone data: ' . $e->getMessage()
            ];
        }
    }

    protected function getCountryCode($query)
    {
        // Simple mapping of common country names to their ISO codes
        $countries = [
            'philippines' => 'PH',
            'united states' => 'US',
            'usa' => 'US',
            'uk' => 'GB',
            'united kingdom' => 'GB',
            'japan' => 'JP',
            'china' => 'CN',
            'india' => 'IN',
            'australia' => 'AU',
            'canada' => 'CA',
            'germany' => 'DE',
            'france' => 'FR',
            'italy' => 'IT',
            'spain' => 'ES',
            'brazil' => 'BR',
            'russia' => 'RU',
            'south korea' => 'KR',
            'korea' => 'KR',
            'mexico' => 'MX',
            'indonesia' => 'ID',
            'malaysia' => 'MY',
            'singapore' => 'SG',
            'thailand' => 'TH',
            'vietnam' => 'VN',
            'new zealand' => 'NZ'
        ];
        
        $query = strtolower(trim($query));
        return $countries[$query] ?? null;
    }

    protected function geocodeCity($city)
    {
        try {
            Log::info('Geocoding city: ' . $city);
            
            // Add user agent as required by Nominatim's usage policy
            $response = Http::withHeaders([
                'User-Agent' => 'PlannerApp/1.0'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $city,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1
            ]);

            Log::info('Geocoding response:', ['response' => $response->json()]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data)) {
                    $location = $data[0];
                    
                    // Get coordinates
                    $coordinates = [
                        'lat' => $location['lat'],
                        'lng' => $location['lon']
                    ];
                    
                    Log::info('Found coordinates for ' . $city, $coordinates);
                    
                    // Use coordinates to get timezone
                    return $this->getTimezoneByCoordinates($coordinates['lat'], $coordinates['lng']);
                }
            }
            
            Log::error('Geocoding failed for: ' . $city);
            return [
                'success' => false,
                'error' => 'Could not find coordinates for this location. Please try a different city name.'
            ];
        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to geocode location: ' . $e->getMessage()
            ];
        }
    }

    protected function getTimezoneByCountryCode($countryCode)
    {
        try {
            $response = Http::get("{$this->baseUrl}/list-time-zone", [
                'key' => $this->apiKey,
                'format' => 'json',
                'country' => $countryCode
            ]);

            Log::info('TimezoneDB Country API Response:', ['response' => $response->json()]);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch timezone data');
            }

            $data = $response->json();
            
            if ($data['status'] === 'OK' && !empty($data['zones'])) {
                // Get the first timezone for the country
                $zone = $data['zones'][0];
                return [
                    'success' => true,
                    'data' => [
                        'timezone' => $zone['zoneName'],
                        'current_time' => date('Y-m-d H:i:s', $zone['timestamp']),
                        'gmt_offset' => $zone['gmtOffset'],
                        'country' => $zone['countryName'],
                        'region' => $zone['regionName'] ?? null
                    ]
                ];
            }

            throw new \Exception($data['message'] ?? 'Unknown error');
        } catch (\Exception $e) {
            Log::error('Country timezone lookup error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to fetch timezone data: ' . $e->getMessage()
            ];
        }
    }

    public function getTimezoneByCoordinates($lat, $lng)
    {
        try {
            Log::info('Getting timezone for coordinates:', ['lat' => $lat, 'lng' => $lng]);
            
            $response = Http::get("{$this->baseUrl}/get-time-zone", [
                'key' => $this->apiKey,
                'format' => 'json',
                'by' => 'position',
                'lat' => $lat,
                'lng' => $lng
            ]);

            Log::info('Coordinate timezone response:', ['response' => $response->json()]);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch timezone data');
            }

            $data = $response->json();
            
            if ($data['status'] === 'OK') {
                return [
                    'success' => true,
                    'data' => [
                        'timezone' => $data['zoneName'],
                        'current_time' => $data['formatted'],
                        'gmt_offset' => $data['gmtOffset'],
                        'country' => $data['countryName'] ?? null,
                        'region' => $data['regionName'] ?? null
                    ]
                ];
            }

            throw new \Exception($data['message'] ?? 'Unknown error');
        } catch (\Exception $e) {
            Log::error('Coordinate timezone lookup error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to fetch timezone data: ' . $e->getMessage()
            ];
        }
    }

    public function listTimezones()
    {
        try {
            $response = Http::get("{$this->baseUrl}/list-time-zone", [
                'key' => $this->apiKey,
                'format' => 'json'
            ]);

            if (!$response->successful()) {
                Log::error("TimezoneDB API error: " . $response->body());
                return [
                    'error' => 'Failed to fetch timezone list',
                    'status' => $response->status()
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("TimezoneDB API error: " . $e->getMessage());
            return [
                'error' => 'Failed to fetch timezone list',
                'status' => 500
            ];
        }
    }
} 