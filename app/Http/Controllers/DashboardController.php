<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $ticketQuery = $this->visibleTicketQuery($request);
        $paymentQuery = $this->visiblePaymentQuery($request);

        $ticketStats = collect(TicketStatus::values())
            ->mapWithKeys(fn (string $status): array => [
                $status => (clone $ticketQuery)->where('status', $status)->count(),
            ]);

        $paymentStats = collect(PaymentStatus::values())
            ->mapWithKeys(fn (string $status): array => [
                $status => (clone $paymentQuery)->where('status', $status)->count(),
            ]);

        $recentTickets = (clone $ticketQuery)
            ->with('user:id,name,email')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Ticket $ticket): array => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'status' => $ticket->getRawOriginal('status'),
                'user' => $ticket->user?->email,
                'created_at' => $ticket->created_at?->toIso8601String(),
            ]);

        $recentPayments = (clone $paymentQuery)
            ->with('user:id,name,email')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'amount' => (string) $payment->amount,
                'status' => $payment->getRawOriginal('status'),
                'user' => $payment->user?->email,
                'created_at' => $payment->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'tickets' => [
                    'total' => (clone $ticketQuery)->count(),
                    'open' => $ticketStats[TicketStatus::OPEN->value],
                    'in_progress' => $ticketStats[TicketStatus::IN_PROGRESS->value],
                    'closed' => $ticketStats[TicketStatus::CLOSED->value],
                ],
                'payments' => [
                    'total' => (clone $paymentQuery)->count(),
                    'pending' => $paymentStats[PaymentStatus::PENDING->value],
                    'paid' => $paymentStats[PaymentStatus::PAID->value],
                    'failed' => $paymentStats[PaymentStatus::FAILED->value],
                    'paid_amount' => (string) (clone $paymentQuery)
                        ->where('status', PaymentStatus::PAID->value)
                        ->sum('amount'),
                ],
            ],
            'recentTickets' => $recentTickets,
            'recentPayments' => $recentPayments,
            'can' => [
                'create_ticket' => $request->user()->can('create', Ticket::class),
                'create_payment' => $request->user()->can('create', Payment::class),
            ],
        ]);
    }

    private function visibleTicketQuery(Request $request): Builder
    {
        $query = Ticket::query();

        if (! $request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    private function visiblePaymentQuery(Request $request): Builder
    {
        $query = Payment::query();

        if (! $request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }
}
