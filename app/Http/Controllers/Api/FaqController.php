<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        $faqs = Faq::query()
            ->active()
            ->apiPublic()
            ->orderBy('category')
            ->orderBy('order')
            ->get()
            ->groupBy('category')
            ->map(
                fn ($group) => $group
                    ->map(fn (Faq $faq) => FaqResource::make($faq)->resolve())
                    ->values()
            );

        return response()->json([
            'faqs' => $faqs,
        ]);
    }
}
