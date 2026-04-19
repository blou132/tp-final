<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ActivityController extends Controller
{
    public function index(Request $request): Response
    {
        $actor = $request->user();
        $isAdmin = $actor->hasRole('admin');

        $entityType = $request->string('entity_type')->toString();
        $action = trim($request->string('action')->toString());
        $actorId = $isAdmin ? (int) $request->integer('actor_id') : 0;

        $sourceUnavailable = false;
        $logs = collect();

        try {
            $query = ActivityLog::query()->orderByDesc('_id');

            if (! $isAdmin) {
                $query->where('actor_id', $actor->id);
            }

            if (in_array($entityType, ['ticket', 'payment'], true)) {
                $query->where('entity_type', $entityType);
            } else {
                $entityType = '';
            }

            if ($action !== '') {
                $query->where('action', 'like', "%{$action}%");
            }

            if ($isAdmin && $actorId > 0) {
                $query->where('actor_id', $actorId);
            } else {
                $actorId = 0;
            }

            $logs = collect($query->limit(120)->get()->all());
        } catch (Throwable $exception) {
            $sourceUnavailable = true;

            Log::warning('Activity logs unavailable in UI', [
                'error' => $exception->getMessage(),
            ]);
        }

        $actorsById = $this->resolveActors($logs);

        return Inertia::render('Activities/Index', [
            'filters' => [
                'entity_type' => $entityType,
                'action' => $action,
                'actor_id' => $actorId,
            ],
            'logs' => $logs->map(function (ActivityLog $log) use ($actorsById): array {
                $actorId = (int) ($log->actor_id ?? 0);
                $resolvedActor = $actorsById[$actorId] ?? null;

                return [
                    'id' => (string) ($log->_id ?? $log->id),
                    'action' => (string) $log->action,
                    'entity_type' => (string) $log->entity_type,
                    'entity_id' => (string) $log->entity_id,
                    'actor_id' => $actorId,
                    'actor' => $resolvedActor,
                    'metadata' => is_array($log->metadata) ? $log->metadata : [],
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            })->values()->all(),
            'source_unavailable' => $sourceUnavailable,
            'can' => [
                'admin_scope' => $isAdmin,
            ],
            'actorOptions' => $isAdmin
                ? User::query()
                    ->orderBy('name')
                    ->get(['id', 'name', 'email'])
                    ->map(fn (User $user): array => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ])
                    ->all()
                : [],
        ]);
    }

    /**
     * @param  Collection<int, ActivityLog>  $logs
     * @return array<int, array{name: string, email: string}>
     */
    private function resolveActors(Collection $logs): array
    {
        $actorIds = $logs
            ->map(fn (ActivityLog $log): int => (int) ($log->actor_id ?? 0))
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($actorIds->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $actorIds)
            ->get(['id', 'name', 'email'])
            ->mapWithKeys(fn (User $user): array => [
                $user->id => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ])
            ->all();
    }
}
