<?php

namespace App\Actions\Helpdesk;

use App\Actions\Uploads\PromoteTemporaryUpload;
use App\Models\TemporaryUpload;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Database\Eloquent\Model;

class AttachUploadedFiles
{
    public function __construct(
        private readonly PromoteTemporaryUpload $promoteTemporaryUpload,
    ) {}

    /**
     * @param  Ticket|TicketComment  $model
     * @param  array<int, string>  $temporaryUploadIds
     */
    public function handle(Model $model, array $temporaryUploadIds): void
    {
        if ($temporaryUploadIds === []) {
            return;
        }

        $temporaryUploads = TemporaryUpload::query()
            ->whereIn('id', $temporaryUploadIds)
            ->get();

        foreach ($temporaryUploads as $temporaryUpload) {
            // Promote file using existing PromoteTemporaryUpload action
            $targetPath = $this->promoteTemporaryUpload->handle($temporaryUpload, 'attachments/attachment');

            $model->attachments()->create([
                'file_path' => $targetPath,
                'original_name' => $temporaryUpload->original_name,
                'mime_type' => $temporaryUpload->mime_type,
                'size' => $temporaryUpload->size,
            ]);
        }
    }
}
