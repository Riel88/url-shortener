<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUrlRequest;
use App\Services\UrlShortenerService;
use Illuminate\Http\JsonResponse;

class UrlController extends Controller
{
    public function __construct(
        private UrlShortenerService $service
    ) {}

    public function store(StoreUrlRequest $request): JsonResponse
    {
        $url = $this->service->shorten($request->input('url'));

        return response()->json([
            'short_code'   => $url->short_code,
            'short_url'    => url('/' . $url->short_code),
            'original_url' => $url->original_url,
        ], 201);
    }

    public function redirect(string $shortCode): mixed
    {
        $url = $this->service->resolve($shortCode);

        if (!$url) {
            return response()->json(['message' => 'URL not found.'], 404);
        }

        $this->service->recordVisit($url);

        return redirect($url->original_url);
    }

    public function stats(string $shortCode): JsonResponse
    {
        $stats = $this->service->getStats($shortCode);

        if (!$stats) {
            return response()->json(['message' => 'URL not found.'], 404);
        }

        return response()->json($stats);
    }
}