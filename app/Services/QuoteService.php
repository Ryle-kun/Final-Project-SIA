<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class QuoteService
{
    protected $apiKey;
    protected $baseUrl = 'https://favqs.com/api';

    public function __construct()
    {
        $this->apiKey = config('services.favqs.api_key');
    }

    protected function getHeaders()
    {
        $headers = [
            'Authorization' => "Token token=\"{$this->apiKey}\"",
            'Content-Type' => 'application/json'
        ];

        if (Auth::check()) {
            $userToken = Auth::user()->favqs_token;
            if ($userToken) {
                $headers['User-Token'] = $userToken;
            }
        }

        return $headers;
    }

    public function getRandomQuote()
    {
        try {
            Log::info('Fetching quote of the day');
            $response = Http::get("{$this->baseUrl}/qotd");

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Quote API Response:', ['data' => $data]);

                if (isset($data['quote'])) {
                    return [
                        'success' => true,
                        'data' => [
                            'body' => $data['quote']['body'],
                            'author' => $data['quote']['author']
                        ]
                    ];
                }
            }

            Log::error('Failed to fetch quote:', ['response' => $response->body()]);
            return [
                'success' => false,
                'error' => 'Failed to fetch quote'
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching quote:', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Failed to fetch quote'
            ];
        }
    }

    public function searchQuotes($query)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/quotes", [
                    'filter' => $query,
                    'type' => 'tag'
                ]);

            if (!$response->successful()) {
                Log::error("FavQs API error: " . $response->body());
                return [
                    'error' => 'Failed to search quotes',
                    'status' => $response->status()
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("FavQs API error: " . $e->getMessage());
            return [
                'error' => 'Failed to search quotes',
                'status' => 500
            ];
        }
    }

    public function getFavoriteQuotes()
    {
        try {
            if (!Auth::check()) {
                return [
                    'error' => 'User must be logged in to view favorites',
                    'status' => 401
                ];
            }

            $response = Http::withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/quotes/favorites");

            if (!$response->successful()) {
                Log::error("FavQs API error: " . $response->body());
                return [
                    'error' => 'Failed to fetch favorites',
                    'status' => $response->status()
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("FavQs API error: " . $e->getMessage());
            return [
                'error' => 'Failed to fetch favorites',
                'status' => 500
            ];
        }
    }

    public function markAsFavorite($quoteId)
    {
        try {
            if (!Auth::check()) {
                return [
                    'error' => 'User must be logged in to mark favorites',
                    'status' => 401
                ];
            }

            $response = Http::withHeaders($this->getHeaders())
                ->put("{$this->baseUrl}/quotes/{$quoteId}/fav");

            if (!$response->successful()) {
                Log::error("FavQs API error: " . $response->body());
                return [
                    'error' => 'Failed to mark quote as favorite',
                    'status' => $response->status()
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("FavQs API error: " . $e->getMessage());
            return [
                'error' => 'Failed to mark quote as favorite',
                'status' => 500
            ];
        }
    }
} 