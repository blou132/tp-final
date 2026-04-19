<?php

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->string('priority', 32)->default(TicketPriority::MEDIUM->value)->after('status');
            $table->string('category', 32)->default(TicketCategory::GENERAL->value)->after('priority');
            $table->timestamp('due_at')->nullable()->after('category');
            $table->foreignId('assigned_to')->nullable()->after('user_id')->constrained('users')->nullOnDelete();

            $table->index(['priority', 'category']);
            $table->index('due_at');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex(['priority', 'category']);
            $table->dropIndex(['due_at']);
            $table->dropIndex(['assigned_to']);
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn(['priority', 'category', 'due_at']);
        });
    }
};
