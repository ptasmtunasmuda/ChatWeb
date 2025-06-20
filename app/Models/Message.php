<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'content',
        'type',
        'reply_to_message_id',
        'is_edited',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replyToMessage()
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    public function readByUsers()
    {
        return $this->belongsToMany(User::class, 'message_reads')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInRoom($query, $roomId)
    {
        return $query->where('chat_room_id', $roomId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Helper methods
     */
    public function markAsRead(User $user)
    {
        if (!$this->isReadBy($user)) {
            $this->readByUsers()->attach($user->id, ['read_at' => now()]);
        }
    }

    public function isReadBy(User $user): bool
    {
        return $this->readByUsers()->where('user_id', $user->id)->exists();
    }

    public function markAsEdited()
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    public function hasAttachments(): bool
    {
        return $this->media()->exists();
    }

    public function getAttachments()
    {
        return $this->getMedia();
    }
}
