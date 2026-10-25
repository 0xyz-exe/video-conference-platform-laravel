<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'room_code',
        'description',
        'passcode',
        'is_locked',
        'max_participants',
        'status',
        'scheduled_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'max_participants' => 'integer',
            'scheduled_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($meeting) {
            if (empty($meeting->room_code)) {
                $meeting->room_code = Str::lower(Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3));
            }
        });
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(Participant::class)->where('status', 'active');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MeetingMessage::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MeetingNote::class);
    }

    public function isHost(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
