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
        'attachment_info',
        'is_system',
        'system_type',
        'system_data',
    ];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'is_system' => 'boolean',
            'edited_at' => 'datetime',
            'attachment_info' => 'array',
            'system_data' => 'array',
        ];
    }

    protected $appends = ['is_deleted'];

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

    public function scopeSystemMessages($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeUserMessages($query)
    {
        return $query->where('is_system', false);
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

    /**
     * Accessor for is_deleted attribute
     */
    public function getIsDeletedAttribute(): bool
    {
        return !is_null($this->deleted_at);
    }
}
