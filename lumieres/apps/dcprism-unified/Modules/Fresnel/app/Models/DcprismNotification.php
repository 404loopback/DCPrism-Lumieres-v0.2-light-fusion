<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DcprismNotification extends Model
{
    protected $table = 'dcprism_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'action_url',
        'action_label',
        'read_at',
        'is_important',
        'festival_id',
        'movie_id',
        'dcp_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_important' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Types de notifications disponibles
     */
    public const TYPES = [
        'movie_uploaded' => [
            'label' => 'Film uploadé',
            'icon' => 'heroicon-o-film',
            'color' => 'blue',
        ],
        'movie_validated' => [
            'label' => 'Film validé',
            'icon' => 'heroicon-o-check-badge',
            'color' => 'green',
        ],
        'movie_rejected' => [
            'label' => 'Film rejeté',
            'icon' => 'heroicon-o-x-circle',
            'color' => 'red',
        ],
        'dcp_ready' => [
            'label' => 'DCP prêt',
            'icon' => 'heroicon-o-play',
            'color' => 'green',
        ],
        'dcp_failed' => [
            'label' => 'DCP échoué',
            'icon' => 'heroicon-o-exclamation-circle',
            'color' => 'red',
        ],
        'festival_deadline' => [
            'label' => 'Deadline festival',
            'icon' => 'heroicon-o-calendar',
            'color' => 'orange',
        ],
        'festival_assigned' => [
            'label' => 'Assigné au festival',
            'icon' => 'heroicon-o-trophy',
            'color' => 'purple',
        ],
        'comment_received' => [
            'label' => 'Nouveau commentaire',
            'icon' => 'heroicon-o-chat-bubble-left',
            'color' => 'blue',
        ],
        'status_changed' => [
            'label' => 'Statut modifié',
            'icon' => 'heroicon-o-arrow-path',
            'color' => 'gray',
        ],
        'system_maintenance' => [
            'label' => 'Maintenance système',
            'icon' => 'heroicon-o-wrench-screwdriver',
            'color' => 'yellow',
        ],
        'account_created' => [
            'label' => 'Compte créé',
            'icon' => 'heroicon-o-user-plus',
            'color' => 'green',
        ],
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function dcp(): BelongsTo
    {
        return $this->belongsTo(Dcp::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Scopes
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeImportant(Builder $query): Builder
    {
        return $query->where('is_important', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Méthodes utilitaires
    public function markAsRead(): bool
    {
        $this->read_at = now();

        return $this->save();
    }

    public function markAsUnread(): bool
    {
        $this->read_at = null;

        return $this->save();
    }

    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function getTypeConfig(): array
    {
        return self::TYPES[$this->type] ?? [
            'label' => $this->type,
            'icon' => 'heroicon-o-bell',
            'color' => 'gray',
        ];
    }

    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedDate(): string
    {
        if ($this->created_at->isToday()) {
            return 'Aujourd\'hui à '.$this->created_at->format('H:i');
        }

        if ($this->created_at->isYesterday()) {
            return 'Hier à '.$this->created_at->format('H:i');
        }

        return $this->created_at->format('d/m/Y à H:i');
    }

    // Méthodes statiques pour création
    public static function createForUser(int $userId, array $data): self
    {
        $typeConfig = self::TYPES[$data['type']] ?? [];

        return self::create(array_merge([
            'user_id' => $userId,
            'icon' => $typeConfig['icon'] ?? 'heroicon-o-bell',
            'color' => $typeConfig['color'] ?? 'gray',
        ], $data));
    }

    public static function createForUsers(array $userIds, array $data): \Illuminate\Support\Collection
    {
        $notifications = collect();

        foreach ($userIds as $userId) {
            $notifications->push(self::createForUser($userId, $data));
        }

        return $notifications;
    }

    public static function getUnreadCountForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->count();
    }

    public static function getRecentForUser(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::forUser($userId)
            ->with(['festival', 'movie', 'createdByUser'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function markAllAsReadForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->update(['read_at' => now()]);
    }
}
