<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketApiController extends Controller
{
    public function openTickets(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->visibleTicketsQuery($request)
            ->open()
            ->with('user:id,name,email')
            ->get();

        return response()->json([
            'data' => $tickets->map(fn (Ticket $ticket): array => $this->serializeTicket($ticket)),
        ]);
    }

    public function closedTickets(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->visibleTicketsQuery($request)
            ->closed()
            ->with('user:id,name,email')
            ->get();

        return response()->json([
            'data' => $tickets->map(fn (Ticket $ticket): array => $this->serializeTicket($ticket)),
        ]);
    }

    public function userTickets(Request $request, string $email): JsonResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $actor = $request->user();

        if (! $actor->hasRole('admin') && $actor->email !== $email) {
            abort(403);
        }

        $targetUser = User::query()->where('email', $email)->firstOrFail();

        $query = Ticket::query()
            ->where('user_id', $targetUser->id)
            ->with('user:id,name,email')
            ->latest();

        if (! $actor->hasRole('admin')) {
            $query->where('user_id', $actor->id);
        }

        $tickets = $query->get();

        return response()->json([
            'data' => $tickets->map(fn (Ticket $ticket): array => $this->serializeTicket($ticket)),
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $query = $this->visibleTicketsQuery($request);
        $total = (clone $query)->count();

        $statusCounts = collect(TicketStatus::values())
            ->mapWithKeys(fn (string $status): array => [
                $status => (clone $query)->where('status', $status)->count(),
            ]);

        $stats = [
            'total' => $total,
            'open' => $statusCounts[TicketStatus::OPEN->value],
            'in_progress' => $statusCounts[TicketStatus::IN_PROGRESS->value],
            'closed' => $statusCounts[TicketStatus::CLOSED->value],
            'closed_ratio' => $total > 0
                ? round(($statusCounts[TicketStatus::CLOSED->value] / $total) * 100, 2)
                : 0.0,
        ];

        if ($request->user()->hasRole('admin')) {
            $stats['by_user'] = Ticket::query()
                ->join('users', 'users.id', '=', 'tickets.user_id')
                ->selectRaw('users.email as email, COUNT(*) as total')
                ->groupBy('users.email')
                ->orderByDesc('total')
                ->get();
        }

        return response()->json([
            'data' => $stats,
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

    private function serializeTicket(Ticket $ticket): array
    {
        return [
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
        ];
    }
}
