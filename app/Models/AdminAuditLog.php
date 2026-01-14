<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminAuditLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'target_type',
        'target_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the admin who performed the action
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the target model
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Log an admin action
     */
    public static function log(
        string $action,
        $target = null,
        array $oldValues = null,
        array $newValues = null,
        string $notes = null
    ): self {
        return self::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'target_type' => $target ? get_class($target) : null,
            'target_id' => $target?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'notes' => $notes,
        ]);
    }

    /**
     * Get human-readable action name
     */
    public function getActionLabelAttribute(): string
    {
        $labels = [
            'user.ban' => 'Banned user',
            'user.unban' => 'Unbanned user',
            'user.role.update' => 'Updated user role',
            'user.status.update' => 'Updated user status',
            'user.profile.update' => 'Updated user profile',
            'course.approve' => 'Approved course',
            'course.reject' => 'Rejected course',
            'course.hide' => 'Hid course',
            'course.restore' => 'Restored course',
            'course.update' => 'Updated course',
            'instructor.verify' => 'Verified instructor',
            'instructor.reject' => 'Rejected instructor',
            'instructor.restrict' => 'Restricted instructor',
            'enrollment.add' => 'Added enrollment',
            'enrollment.remove' => 'Removed enrollment',
            'review.remove' => 'Removed review',
            'question.remove' => 'Removed question',
            'answer.remove' => 'Removed answer',
            'setting.update' => 'Updated settings',
            'category.create' => 'Created category',
            'category.update' => 'Updated category',
            'category.delete' => 'Deleted category',
        ];

        return $labels[$this->action] ?? ucwords(str_replace('.', ' ', $this->action));
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $limit = 20)
    {
        return $query->with('admin:id,name')
            ->orderByDesc('created_at')
            ->limit($limit);
    }
}
