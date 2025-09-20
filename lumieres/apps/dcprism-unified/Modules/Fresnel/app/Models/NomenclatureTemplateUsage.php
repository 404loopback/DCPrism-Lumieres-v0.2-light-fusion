<?php

namespace Modules\Fresnel\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NomenclatureTemplateUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomenclature_template_id',
        'used_by',
        'used_at',
        'ip_address',
        'user_agent',
        'context',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation vers le template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NomenclatureTemplate::class, 'nomenclature_template_id');
    }

    /**
     * Relation vers l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Scope pour les usages récents
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('used_at', '>=', now()->subDays($days));
    }

    /**
     * Scope pour un template spécifique
     */
    public function scopeForTemplate($query, int $templateId)
    {
        return $query->where('nomenclature_template_id', $templateId);
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('used_by', $userId);
    }

    /**
     * Créer un nouvel usage avec les données de contexte
     */
    public static function recordUsage(
        int $templateId,
        ?int $userId = null,
        array $context = []
    ): self {
        return self::create([
            'nomenclature_template_id' => $templateId,
            'used_by' => $userId ?? auth()->id(),
            'used_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => array_merge($context, [
                'session_id' => session()->getId(),
                'url' => request()->url(),
            ]),
        ]);
    }
}
