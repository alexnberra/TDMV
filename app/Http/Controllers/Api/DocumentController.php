<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Application;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function store(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'document_type' => 'required|in:insurance,title,tribal_id,drivers_license,inspection,proof_of_residency,other',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $file = $request->file('file');
        $disk = config('filesystems.default');
        $path = $file->store("documents/{$application->id}", $disk);

        $document = $application->documents()->create([
            'user_id' => $request->user()->id,
            'document_type' => $validated['document_type'],
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'uploaded',
            'uploaded_at' => now(),
        ]);

        $application->timeline()->create([
            'event_type' => 'document_uploaded',
            'description' => "Document uploaded: {$validated['document_type']}",
            'performed_by' => $request->user()->id,
        ]);

        return response()->json([
            'document' => DocumentResource::make(
                Document::query()
                    ->apiList()
                    ->whereKey($document->id)
                    ->firstOrFail()
            )->resolve(),
        ], 201);
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        return response()->json([
            'document' => DocumentResource::make($document)->resolve(),
        ]);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        Storage::disk(config('filesystems.default'))->delete($document->file_path);
        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        return Storage::disk(config('filesystems.default'))->download($document->file_path, $document->file_name);
    }
}
