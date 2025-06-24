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

    // Remove appends and handle this in controller
    // protected $appends = ['latest_message'];

    /**
     * Accessor for latest_message attribute that handles deleted messages properly
     */
    public function getLatestMessageAttribute()
    {
        // Get the latest message overall (including deleted ones)
        if (!$this->relationLoaded('latestMessageOverall')) {
            $this->load('latestMessageOverall.user');
        }
        
        $latestOverall = $this->latestMessageOverall;
        
        // If the latest overall message is deleted, return it (to show "deleted" status)
        if ($latestOverall && $latestOverall->trashed()) {
            return $latestOverall;
        }
        
        // If the latest overall message is not deleted, return it
        if ($latestOverall && !$latestOverall->trashed()) {
            return $latestOverall;
        }
        
        // Fallback: get latest active message
        if (!$this->relationLoaded('latestActiveMessage')) {
            $this->load('latestActiveMessage.user');
        }
        
        return $this->latestActiveMessage;
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

    public function latestActiveMessage()
    {
        return $this->hasOne(Message::class)->whereNull('deleted_at')->latest();
    }

    public function latestMessageOverall()
    {
        return $this->hasOne(Message::class)->withTrashed()->latest();
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
