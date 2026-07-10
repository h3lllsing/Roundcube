<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Attachment>
     */
    public function listFor(mixed $notable = null, array $filters = []): LengthAwarePaginator
    {
        if ($notable) {
            $query = $notable->attachments();
        } else {
            $query = Attachment::whereNull('notable_type');
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
        }

        if (isset($filters['search'])) {
            $query->where('original_name', 'like', '%'.$filters['search'].'%');
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSort = ['created_at', 'updated_at', 'original_name', 'size'];
        if (! in_array($sortBy, $allowedSort)) {
            $sortBy = 'created_at';
        }
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        return $query->with('user')->orderBy($sortBy, $sortOrder)
            ->paginate(min($filters['per_page'] ?? 50, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, mixed $notable = null): Attachment
    {
        /** @var UploadedFile $file */
        $file = $data['file'];
        $storedPath = $file->store('attachments', 'public');
        if ($storedPath === false) {
            throw new \RuntimeException('Failed to store file');
        }

        $attachment = new Attachment([
            'user_id' => $data['user_id'],
            'filename' => basename($storedPath),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        if ($notable) {
            $attachment->notable()->associate($notable);
        }

        $attachment->save();
        $attachment->load('user');

        return $attachment;
    }

    public function delete(Attachment $attachment): void
    {
        $attachment->delete();
    }

    public function forceDelete(Attachment $attachment): void
    {
        $filename = $attachment->filename;
        Storage::disk('public')->delete('attachments/'.$filename);
        $attachment->forceDelete();
    }
}
