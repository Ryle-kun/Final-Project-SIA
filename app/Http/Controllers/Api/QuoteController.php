<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuoteController extends Controller
{
    protected $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
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

    public function getRandomQuote()
    {
        try {
            Log::info('Fetching random quote');
            $quote = $this->quoteService->getRandomQuote();
            Log::info('Quote service response:', ['response' => $quote]);

            return response()->json($quote);
        } catch (\Exception $e) {
            Log::error('Error in getRandomQuote:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching the quote'
            ], 500);
        }
    }

    public function searchQuotes(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $quotes = $this->quoteService->searchQuotes($request->query);
        return response()->json($quotes);
    }

    public function getFavorites()
    {
        $quotes = $this->quoteService->getFavoriteQuotes();
        return response()->json($quotes);
    }

    public function markAsFavorite(string $quote)
    {
        $result = $this->quoteService->markAsFavorite($quote);
        return response()->json($result);
    }
}
