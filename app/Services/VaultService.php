<?php

namespace App\Services;

use App\Events\VaultPasswordRevealed;
use App\Models\User;
use App\Models\VaultEntry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VaultService
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, VaultEntry>
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = VaultEntry::with('module.feature', 'user');

        if (! empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        if (isset($filters['module_id'])) {
            $query->where('module_id', $filters['module_id']);
        }
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('service_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('service_url', 'like', '%'.$filters['search'].'%')
                    ->orWhere('username', 'like', '%'.$filters['search'].'%');
            });
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        $accessibleModuleIds = $filters['accessible_module_ids'] ?? [];
        if ($accessibleModuleIds instanceof Collection) {
            $accessibleModuleIds = $accessibleModuleIds->toArray();
        }
        if (! empty($accessibleModuleIds)) {
            $query->whereIn('module_id', $accessibleModuleIds);
        }

        $sortBy = $filters['sort_by'] ?? 'service_name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $allowedSort = ['service_name', 'created_at', 'updated_at', 'username'];
        if (! in_array($sortBy, $allowedSort)) {
            $sortBy = 'service_name';
        }
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): VaultEntry
    {
        $plainPassword = $data['password'];
        unset($data['password']);

        $entry = new VaultEntry($data);
        $entry->encryptPassword($plainPassword);
        $entry->save();

        return $entry->fresh()->load('module.feature', 'user');
    }

    /** @param array<string, mixed> $data */
    public function update(VaultEntry $entry, array $data): VaultEntry
    {
        if (isset($data['password'])) {
            $plainPassword = $data['password'];
            unset($data['password']);
            $entry->encryptPassword($plainPassword);
        }

        $entry->update($data);
        $entry->refresh();
        $entry->load('module.feature', 'user');

        return $entry;
    }

    public function reveal(VaultEntry $entry, ?User $causer = null): string
    {
        $plain = $entry->decryptPassword();

        if ($causer) {
            activity()
                ->performedOn($entry)
                ->causedBy($causer)
                ->withProperties(['service' => $entry->service_name])
                ->event('revealed')
                ->log('vault_entry_revealed');
            VaultPasswordRevealed::dispatch($entry, $causer);
            $this->webhookService->fire('vault.revealed', $entry);
        }

        return $plain;
    }

    public function delete(VaultEntry $entry): void
    {
        $entry->delete();
    }
}
