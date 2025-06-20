<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'created_by',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'chat_room_participants')
                    ->withPivot(['role', 'joined_at', 'left_at', 'is_active'])
                    ->withTimestamps();
    }

    public function activeParticipants()
    {
        return $this->participants()->wherePivot('is_active', true);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('chat_rooms.is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Helper methods
     */
    public function addParticipant(User $user, string $role = 'member')
    {
        return $this->participants()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
            'is_active' => true,
        ]);
    }

    public function removeParticipant(User $user)
    {
        return $this->participants()->updateExistingPivot($user->id, [
            'left_at' => now(),
            'is_active' => false,
        ]);
    }

    public function isParticipant(User $user): bool
    {
        return $this->activeParticipants()->where('users.id', $user->id)->exists();
    }

    public function getParticipantRole(User $user): ?string
    {
        $participant = $this->participants()
                           ->where('users.id', $user->id)
                           ->wherePivot('is_active', true)
                           ->first();

        return $participant?->pivot->role;
    }
}
