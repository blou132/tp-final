<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payment::class);

        $user = $request->user();

        $query = $this->visiblePaymentsQuery($request)
            ->with('user')
            ->latest();

        $filters = $this->applyIndexFilters($query, $request);

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
                'can' => [
                    'view' => $user->can('view', $payment),
                    'update' => $user->can('update', $payment),
                    'delete' => $user->can('delete', $payment),
                ],
            ]),
            'statuses' => PaymentStatus::values(),
            'filters' => $filters,
            'can' => [
                'create' => $user->can('create', Payment::class),
                'export' => $user->can('viewAny', Payment::class),
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

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Payment::class);

        $query = $this->visiblePaymentsQuery($request)
            ->with('user:id,name,email')
            ->latest();

        $this->applyIndexFilters($query, $request);

        $payments = $query->get();
        $filename = sprintf('payments-%s.csv', now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($payments): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'id',
                'amount',
                'status',
                'owner_email',
                'created_at',
            ]);

            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->id,
                    (string) $payment->amount,
                    $payment->getRawOriginal('status'),
                    $payment->user?->email,
                    $payment->created_at?->toIso8601String(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function visiblePaymentsQuery(Request $request): Builder
    {
        $query = Payment::query();

        if (! $request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    /**
     * @return array{status: string, q: string}
     */
    private function applyIndexFilters(Builder $query, Request $request): array
    {
        $status = $request->string('status')->toString();
        if ($status !== '' && in_array($status, PaymentStatus::values(), true)) {
            $query->where('status', $status);
        } else {
            $status = '';
        }

        $search = trim($request->string('q')->toString());
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->whereHas('user', function (Builder $userQuery) use ($search): void {
                        $userQuery
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });

                if (is_numeric($search)) {
                    $builder
                        ->orWhere('id', (int) $search)
                        ->orWhere('amount', (float) $search);
                }
            });
        }

        return [
            'status' => $status,
            'q' => $search,
        ];
    }
}
