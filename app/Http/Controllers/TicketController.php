<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use App\Services\ActivityLogService;
use App\Services\ProfanityFilterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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

        $query = Ticket::query()->with('user')->latest();

        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $statusFilter = $request->string('status')->toString();
        if ($statusFilter !== '' && in_array($statusFilter, TicketStatus::values(), true)) {
            $query->where('status', $statusFilter);
        }

        return Inertia::render('Tickets/Index', [
            'tickets' => $query->paginate(10)->withQueryString()->through(fn (Ticket $ticket): array => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->getRawOriginal('status'),
                'is_flagged' => $ticket->is_flagged,
                'user' => [
                    'id' => $ticket->user?->id,
                    'name' => $ticket->user?->name,
                    'email' => $ticket->user?->email,
                ],
                'created_at' => $ticket->created_at?->toIso8601String(),
                'can' => [
                    'view' => $user->can('view', $ticket),
                    'update' => $user->can('update', $ticket),
                    'delete' => $user->can('delete', $ticket),
                ],
            ]),
            'statuses' => TicketStatus::values(),
            'filters' => [
                'status' => $statusFilter,
            ],
            'can' => [
                'create' => $user->can('create', Ticket::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('Tickets/Create', [
            'statuses' => TicketStatus::values(),
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
            'is_flagged' => $isFlagged,
        ]);

        $this->activityLogService->log(
            action: 'ticket.created',
            entityType: 'ticket',
            entityId: $ticket->id,
            actorId: $request->user()->id,
            metadata: [
                'status' => $ticket->getRawOriginal('status'),
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

        $ticket->load('user');

        return Inertia::render('Tickets/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->getRawOriginal('status'),
                'is_flagged' => $ticket->is_flagged,
                'user' => [
                    'id' => $ticket->user?->id,
                    'name' => $ticket->user?->name,
                    'email' => $ticket->user?->email,
                ],
                'created_at' => $ticket->created_at?->toIso8601String(),
                'updated_at' => $ticket->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function edit(Ticket $ticket): Response
    {
        $this->authorize('update', $ticket);

        return Inertia::render('Tickets/Edit', [
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->getRawOriginal('status'),
                'is_flagged' => $ticket->is_flagged,
            ],
            'statuses' => TicketStatus::values(),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validated();

        $oldStatus = $ticket->getRawOriginal('status');
        $sanitizedTitle = $this->profanityFilter->sanitize($validated['title']);
        $sanitizedDescription = $this->profanityFilter->sanitize($validated['description']);
        $isFlagged = $sanitizedTitle !== $validated['title'] || $sanitizedDescription !== $validated['description'];

        $ticket->update([
            'title' => $sanitizedTitle,
            'description' => $sanitizedDescription,
            'status' => $validated['status'],
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
}
