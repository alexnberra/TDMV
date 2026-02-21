<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDocumentController extends Controller
{
    public function review(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
            'rejection_reason' => 'nullable|string',
        ]);

        $document->update([
            ...$validated,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json([
            'document' => DocumentResource::make(
                Document::query()
                    ->apiList()
                    ->whereKey($document->id)
                    ->firstOrFail()
            )->resolve(),
        ]);
    }
}
