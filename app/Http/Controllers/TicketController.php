<?php

namespace App\Http\Controllers;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ProfanityFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    public function __construct(
        private readonly ProfanityFilterService $profanityFilter,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $user = $request->user();
        $query = $this->visibleTicketsQuery($request)
            ->with(['user', 'assignee'])
            ->latest();

        $filters = $this->applyIndexFilters($query, $request);

        return Inertia::render('Tickets/Index', [
            'tickets' => $query->paginate(10)->withQueryString()->through(fn (Ticket $ticket): array => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->getRawOriginal('status'),
                'priority' => $ticket->getRawOriginal('priority'),
                'category' => $ticket->getRawOriginal('category'),
                'due_at' => $ticket->due_at?->toIso8601String(),
                'is_flagged' => $ticket->is_flagged,
                'user' => [
                    'id' => $ticket->user?->id,
                    'name' => $ticket->user?->name,
                    'email' => $ticket->user?->email,
                ],
                'assignee' => [
                    'id' => $ticket->assignee?->id,
                    'name' => $ticket->assignee?->name,
                    'email' => $ticket->assignee?->email,
                ],
                'created_at' => $ticket->created_at?->toIso8601String(),
                'can' => [
                    'view' => $user->can('view', $ticket),
                    'update' => $user->can('update', $ticket),
                    'delete' => $user->can('delete', $ticket),
                ],
            ]),
            'statuses' => TicketStatus::values(),
            'priorities' => TicketPriority::values(),
            'categories' => TicketCategory::values(),
            'filters' => $filters,
            'can' => [
                'create' => $user->can('create', Ticket::class),
                'export' => $user->can('viewAny', Ticket::class),
                'assign' => $user->hasRole('admin'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('Tickets/Create', [
            'statuses' => TicketStatus::values(),
            'priorities' => TicketPriority::values(),
            'categories' => TicketCategory::values(),
            'assignableUsers' => $this->assignableUsers($request),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $sanitizedTitle = $this->profanityFilter->sanitize($validated['title']);
        $sanitizedDescription = $this->profanityFilter->sanitize($validated['description']);
        $isFlagged = $sanitizedTitle !== $validated['title'] || $sanitizedDescription !== $validated['description'];

        $ticket = $request->user()->tickets()->create([
            'title' => $sanitizedTitle,
            'description' => $sanitizedDescription,
            'status' => $validated['status'],
            'priority' => $validated['priority'] ?? TicketPriority::MEDIUM->value,
            'category' => $validated['category'] ?? TicketCategory::GENERAL->value,
            'due_at' => $validated['due_at'] ?? null,
            'assigned_to' => $request->user()->hasRole('admin') ? ($validated['assigned_to'] ?? null) : null,
            'is_flagged' => $isFlagged,
        ]);

        $this->activityLogService->log(
            action: 'ticket.created',
            entityType: 'ticket',
            entityId: $ticket->id,
            actorId: $request->user()->id,
            metadata: [
                'status' => $ticket->getRawOriginal('status'),
                'priority' => $ticket->getRawOriginal('priority'),
                'category' => $ticket->getRawOriginal('category'),
                'due_at' => $ticket->due_at?->toIso8601String(),
                'assigned_to' => $ticket->assigned_to,
                'is_flagged' => $ticket->is_flagged,
            ],
        );

        return redirect()
            ->route('tickets.index')
            ->with('success', __('messages.flash.ticket_created'));
    }

    public function show(Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load(['user', 'assignee']);

        return Inertia::render('Tickets/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->getRawOriginal('status'),
                'priority' => $ticket->getRawOriginal('priority'),
                'category' => $ticket->getRawOriginal('category'),
                'due_at' => $ticket->due_at?->toIso8601String(),
                'is_flagged' => $ticket->is_flagged,
                'user' => [
                    'id' => $ticket->user?->id,
                    'name' => $ticket->user?->name,
                    'email' => $ticket->user?->email,
                ],
                'assignee' => [
                    'id' => $ticket->assignee?->id,
                    'name' => $ticket->assignee?->name,
                    'email' => $ticket->assignee?->email,
                ],
                'created_at' => $ticket->created_at?->toIso8601String(),
                'updated_at' => $ticket->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function edit(Request $request, Ticket $ticket): Response
    {
        $this->authorize('update', $ticket);

        return Inertia::render('Tickets/Edit', [
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->getRawOriginal('status'),
                'priority' => $ticket->getRawOriginal('priority'),
                'category' => $ticket->getRawOriginal('category'),
                'due_at' => $ticket->due_at?->format('Y-m-d\TH:i'),
                'assigned_to' => $ticket->assigned_to,
                'is_flagged' => $ticket->is_flagged,
            ],
            'statuses' => TicketStatus::values(),
            'priorities' => TicketPriority::values(),
            'categories' => TicketCategory::values(),
            'assignableUsers' => $this->assignableUsers($request),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validated();

        $oldStatus = $ticket->getRawOriginal('status');
        $oldPriority = $ticket->getRawOriginal('priority');
        $oldCategory = $ticket->getRawOriginal('category');

        $sanitizedTitle = $this->profanityFilter->sanitize($validated['title']);
        $sanitizedDescription = $this->profanityFilter->sanitize($validated['description']);
        $isFlagged = $sanitizedTitle !== $validated['title'] || $sanitizedDescription !== $validated['description'];

        $ticket->update([
            'title' => $sanitizedTitle,
            'description' => $sanitizedDescription,
            'status' => $validated['status'],
            'priority' => $validated['priority'] ?? $oldPriority,
            'category' => $validated['category'] ?? $oldCategory,
            'due_at' => $validated['due_at'] ?? null,
            'assigned_to' => $request->user()->hasRole('admin') ? ($validated['assigned_to'] ?? null) : $ticket->assigned_to,
            'is_flagged' => $isFlagged,
        ]);

        $this->activityLogService->log(
            action: 'ticket.updated',
            entityType: 'ticket',
            entityId: $ticket->id,
            actorId: $request->user()->id,
            metadata: [
                'old_status' => $oldStatus,
                'new_status' => $ticket->getRawOriginal('status'),
                'old_priority' => $oldPriority,
                'new_priority' => $ticket->getRawOriginal('priority'),
                'old_category' => $oldCategory,
                'new_category' => $ticket->getRawOriginal('category'),
                'due_at' => $ticket->due_at?->toIso8601String(),
                'assigned_to' => $ticket->assigned_to,
                'is_flagged' => $ticket->is_flagged,
            ],
        );

        return redirect()
            ->route('tickets.index')
            ->with('success', __('messages.flash.ticket_updated'));
    }

    public function destroy(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $ticketId = $ticket->id;
        $ticket->delete();

        $this->activityLogService->log(
            action: 'ticket.deleted',
            entityType: 'ticket',
            entityId: $ticketId,
            actorId: $request->user()->id,
        );

        return redirect()
            ->route('tickets.index')
            ->with('success', __('messages.flash.ticket_deleted'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $query = $this->visibleTicketsQuery($request)
            ->with(['user:id,name,email', 'assignee:id,name,email'])
            ->latest();

        $this->applyIndexFilters($query, $request);

        $tickets = $query->get();

        $filename = sprintf('tickets-%s.csv', now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($tickets): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'id',
                'title',
                'status',
                'priority',
                'category',
                'due_at',
                'owner_email',
                'assignee_email',
                'created_at',
            ]);

            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $ticket->id,
                    $ticket->title,
                    $ticket->getRawOriginal('status'),
                    $ticket->getRawOriginal('priority'),
                    $ticket->getRawOriginal('category'),
                    $ticket->due_at?->toIso8601String(),
                    $ticket->user?->email,
                    $ticket->assignee?->email,
                    $ticket->created_at?->toIso8601String(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function visibleTicketsQuery(Request $request): Builder
    {
        $query = Ticket::query();

        if (! $request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    /**
     * @return array{status: string, priority: string, category: string, q: string}
     */
    private function applyIndexFilters(Builder $query, Request $request): array
    {
        $status = $request->string('status')->toString();
        if ($status !== '' && in_array($status, TicketStatus::values(), true)) {
            $query->where('status', $status);
        } else {
            $status = '';
        }

        $priority = $request->string('priority')->toString();
        if ($priority !== '' && in_array($priority, TicketPriority::values(), true)) {
            $query->where('priority', $priority);
        } else {
            $priority = '';
        }

        $category = $request->string('category')->toString();
        if ($category !== '' && in_array($category, TicketCategory::values(), true)) {
            $query->where('category', $category);
        } else {
            $category = '';
        }

        $search = trim($request->string('q')->toString());
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                        $userQuery
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignee', function (Builder $assigneeQuery) use ($search): void {
                        $assigneeQuery
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });

                if (is_numeric($search)) {
                    $builder->orWhere('id', (int) $search);
                }
            });
        }

        return [
            'status' => $status,
            'priority' => $priority,
            'category' => $category,
            'q' => $search,
        ];
    }

    /**
     * @return array<int, array{id:int,name:string,email:string}>
     */
    private function assignableUsers(Request $request): array
    {
        if (! $request->user()->hasRole('admin')) {
            return [];
        }

        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->all();
    }
}
