<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    public const STATUS_REPLIED = 'replied';

    public const STATUS_ARCHIVED = 'archived';

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }

    /**
     * Available statuses and their human-readable labels.
     *
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_READ => 'Read',
            self::STATUS_REPLIED => 'Replied',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * Scope a query to only include unread/new inquiries.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope a query to a specific status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Mark the inquiry as read.
     */
    public function markAsRead(): self
    {
        if ($this->status === self::STATUS_NEW) {
            $this->status = self::STATUS_READ;
            $this->handled_at = $this->handled_at ?? now();
            $this->save();
        }

        return $this;
    }

    /**
     * Mark the inquiry as replied.
     */
    public function markAsReplied(): self
    {
        $this->status = self::STATUS_REPLIED;
        $this->handled_at = $this->handled_at ?? now();
        $this->save();

        return $this;
    }

    /**
     * Mark the inquiry as archived.
     */
    public function markAsArchived(): self
    {
        $this->status = self::STATUS_ARCHIVED;
        $this->save();

        return $this;
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isRead(): bool
    {
        return $this->status === self::STATUS_READ;
    }

    public function isReplied(): bool
    {
        return $this->status === self::STATUS_REPLIED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }
}
