<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payment::class);

        $user = $request->user();

        $query = Payment::query()->with('user')->latest();

        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $statusFilter = $request->string('status')->toString();
        if ($statusFilter !== '' && in_array($statusFilter, PaymentStatus::values(), true)) {
            $query->where('status', $statusFilter);
        }

        return Inertia::render('Payments/Index', [
            'payments' => $query->paginate(10)->withQueryString()->through(fn (Payment $payment): array => [
                'id' => $payment->id,
                'amount' => (string) $payment->amount,
                'status' => $payment->getRawOriginal('status'),
                'user' => [
                    'id' => $payment->user?->id,
                    'name' => $payment->user?->name,
                    'email' => $payment->user?->email,
                ],
                'created_at' => $payment->created_at?->toIso8601String(),
            ]),
            'statuses' => PaymentStatus::values(),
            'filters' => [
                'status' => $statusFilter,
            ],
            'can' => [
                'create' => $user->can('create', Payment::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Payment::class);

        return Inertia::render('Payments/Create', [
            'statuses' => PaymentStatus::values(),
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $payment = $request->user()->payments()->create($validated);

        $this->activityLogService->log(
            action: 'payment.created',
            entityType: 'payment',
            entityId: $payment->id,
            actorId: $request->user()->id,
            metadata: [
                'status' => $payment->getRawOriginal('status'),
                'amount' => (string) $payment->amount,
            ],
        );

        return redirect()
            ->route('payments.index')
            ->with('success', __('messages.flash.payment_created'));
    }

    public function show(Payment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load('user');

        return Inertia::render('Payments/Show', [
            'payment' => [
                'id' => $payment->id,
                'amount' => (string) $payment->amount,
                'status' => $payment->getRawOriginal('status'),
                'user' => [
                    'id' => $payment->user?->id,
                    'name' => $payment->user?->name,
                    'email' => $payment->user?->email,
                ],
                'created_at' => $payment->created_at?->toIso8601String(),
                'updated_at' => $payment->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function edit(Payment $payment): Response
    {
        $this->authorize('update', $payment);

        return Inertia::render('Payments/Edit', [
            'payment' => [
                'id' => $payment->id,
                'amount' => (string) $payment->amount,
                'status' => $payment->getRawOriginal('status'),
            ],
            'statuses' => PaymentStatus::values(),
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validated();

        $oldStatus = $payment->getRawOriginal('status');
        $payment->update($validated);

        $this->activityLogService->log(
            action: 'payment.updated',
            entityType: 'payment',
            entityId: $payment->id,
            actorId: $request->user()->id,
            metadata: [
                'old_status' => $oldStatus,
                'new_status' => $payment->getRawOriginal('status'),
                'amount' => (string) $payment->amount,
            ],
        );

        return redirect()
            ->route('payments.index')
            ->with('success', __('messages.flash.payment_updated'));
    }

    public function destroy(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $paymentId = $payment->id;
        $payment->delete();

        $this->activityLogService->log(
            action: 'payment.deleted',
            entityType: 'payment',
            entityId: $paymentId,
            actorId: $request->user()->id,
        );

        return redirect()
            ->route('payments.index')
            ->with('success', __('messages.flash.payment_deleted'));
    }
}
