<?php

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'category',
        'due_at',
        'user_id',
        'assigned_to',
        'is_flagged',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'category' => TicketCategory::class,
            'due_at' => 'datetime',
            'is_flagged' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', TicketStatus::OPEN->value);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', TicketStatus::CLOSED->value);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [TicketStatus::OPEN->value, TicketStatus::IN_PROGRESS->value])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }
}
