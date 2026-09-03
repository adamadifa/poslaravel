<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the Auditable trait for a model.
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $user = auth()->user();
            $modelName = class_basename($model);

            AuditTrail::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'System',
                'action' => 'created',
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'description' => "Menambahkan {$modelName} #{$model->getKey()}",
                'old_values' => null,
                'new_values' => $model->getAuditableAttributes(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::updated(function (Model $model) {
            $user = auth()->user();
            $modelName = class_basename($model);
            
            // Only log if something actually changed
            $changes = $model->getChanges();
            // Exclude updated_at from triggering unnecessary audit logs if it's the only changed field
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $original = [];
            foreach (array_keys($changes) as $key) {
                $original[$key] = $model->getOriginal($key);
            }

            AuditTrail::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'System',
                'action' => 'updated',
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'description' => "Memperbarui {$modelName} #{$model->getKey()}",
                'old_values' => $original,
                'new_values' => $changes,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::deleted(function (Model $model) {
            $user = auth()->user();
            $modelName = class_basename($model);

            AuditTrail::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'System',
                'action' => 'deleted',
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'description' => "Menghapus {$modelName} #{$model->getKey()}",
                'old_values' => $model->getAuditableAttributes(),
                'new_values' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    /**
     * Get auditable attributes, filtering out hidden or sensitive fields.
     */
    public function getAuditableAttributes(): array
    {
        $attributes = $this->attributesToArray();
        $sensitive = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

        foreach ($sensitive as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }
}
