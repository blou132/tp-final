<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Ticket;
use App\Policies\PaymentPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->ensureCompiledViewPathIsValid();
    }

    public function boot(): void
    {
        $this->ensureCompiledViewPathIsValid();

        Vite::prefetch(concurrency: 3);

        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
    }

    private function ensureCompiledViewPathIsValid(): void
    {
        $compiledPath = config('view.compiled');

        if (! is_string($compiledPath) || trim($compiledPath) === '') {
            $compiledPath = storage_path('framework/views');
            config(['view.compiled' => $compiledPath]);
        }

        File::ensureDirectoryExists($compiledPath);
    }
}
