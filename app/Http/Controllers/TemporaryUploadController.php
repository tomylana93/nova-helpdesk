<?php

namespace App\Http\Controllers;

use App\Actions\Uploads\DeleteTemporaryUpload;
use App\Actions\Uploads\StoreTemporaryUpload;
use App\Http\Requests\StoreTemporaryUploadRequest;
use App\Models\TemporaryUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TemporaryUploadController extends Controller
{
    public function store(
        StoreTemporaryUploadRequest $request,
        StoreTemporaryUpload $storeTemporaryUpload,
    ): JsonResponse {
        $temporaryUpload = $storeTemporaryUpload->handle(
            $request->file('file'),
            $request->user()->id,
        );

        return response()->json([
            'id' => $temporaryUpload->id,
            'name' => $temporaryUpload->original_name,
            'size' => $temporaryUpload->size,
            'type' => $temporaryUpload->mime_type,
        ], 201);
    }

    public function destroy(
        TemporaryUpload $temporaryUpload,
        DeleteTemporaryUpload $deleteTemporaryUpload,
    ): Response {
        abort_unless($temporaryUpload->user_id === auth()->id(), 403);

        $deleteTemporaryUpload->handle($temporaryUpload);

        return response()->noContent();
    }
}
