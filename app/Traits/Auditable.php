<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->auditCreated();
        });

        static::updated(function ($model) {
            $model->auditUpdated();
        });

        static::deleted(function ($model) {
            $model->auditDeleted();
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->auditRestored();
            });
        }
    }

    /**
     * Get all audits for this model
     */
    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable')->latest();
    }

    /**
     * Audit the creation of a model
     */
    protected function auditCreated()
    {
        if ($this->isAuditingDisabled()) {
            return;
        }
        
        $this->writeAudit('created', [], $this->getAuditableAttributes());
    }

    /**
     * Audit the update of a model
     */
    protected function auditUpdated()
    {
        if ($this->isAuditingDisabled()) {
            return;
        }
        
        $oldValues = [];
        $newValues = [];

        foreach ($this->getDirty() as $key => $newValue) {
            if ($this->shouldAuditAttribute($key)) {
                $oldValues[$key] = $this->getOriginal($key);
                $newValues[$key] = $newValue;
            }
        }

        if (!empty($oldValues) || !empty($newValues)) {
            $this->writeAudit('updated', $oldValues, $newValues);
        }
    }

    /**
     * Audit the deletion of a model
     */
    protected function auditDeleted()
    {
        if ($this->isAuditingDisabled()) {
            return;
        }
        
        $this->writeAudit('deleted', $this->getAuditableAttributes(), []);
    }

    /**
     * Audit the restoration of a model
     */
    protected function auditRestored()
    {
        if ($this->isAuditingDisabled()) {
            return;
        }
        
        $this->writeAudit('restored', [], $this->getAuditableAttributes());
    }

    /**
     * Write an audit entry
     */
    protected function writeAudit(string $event, array $oldValues = [], array $newValues = [])
    {
        $user = auth()->user();
        $request = request();

        $auditData = [
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'url' => $request ? $request->fullUrl() : null,
        ];

        // Add user information if authenticated
        if ($user) {
            $auditData['user_id'] = $user->id;
            $auditData['user_name'] = $user->name;
            $auditData['user_email'] = $user->email;
        }

        // Add farm context if the model has farm_id or current farm
        $farmId = $this->getFarmIdForAudit();
        if ($farmId) {
            $auditData['farm_id'] = $farmId;
        }

        // Add any additional metadata
        $metadata = $this->getAuditMetadata();
        if (!empty($metadata)) {
            $auditData['metadata'] = $metadata;
        }

        Audit::create($auditData);
    }

    /**
     * Get auditable attributes (exclude sensitive/unnecessary fields)
     */
    protected function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();
        
        // Remove attributes that shouldn't be audited
        $excludeAttributes = array_merge(
            $this->getAuditExclude(),
            ['created_at', 'updated_at', 'deleted_at']
        );

        return array_diff_key($attributes, array_flip($excludeAttributes));
    }

    /**
     * Check if an attribute should be audited
     */
    protected function shouldAuditAttribute(string $attribute): bool
    {
        // Don't audit excluded attributes
        if (in_array($attribute, $this->getAuditExclude())) {
            return false;
        }

        // Don't audit timestamps by default
        if (in_array($attribute, ['created_at', 'updated_at', 'deleted_at'])) {
            return false;
        }

        // If auditInclude is defined, only audit those attributes
        if (!empty($this->getAuditInclude())) {
            return in_array($attribute, $this->getAuditInclude());
        }

        return true;
    }

    /**
     * Get farm ID for audit context
     */
    protected function getFarmIdForAudit(): ?string
    {
        // If model has farm_id, use it
        if (isset($this->attributes['farm_id'])) {
            return $this->attributes['farm_id'];
        }

        // If user has current farm, use it
        $user = auth()->user();
        if ($user && isset($user->current_farm_id)) {
            return $user->current_farm_id;
        }

        return null;
    }

    /**
     * Get additional metadata for audit
     */
    protected function getAuditMetadata(): array
    {
        return [];
    }

    /**
     * Get attributes to exclude from auditing
     */
    protected function getAuditExclude(): array
    {
        return property_exists($this, 'auditExclude') ? $this->auditExclude : [];
    }

    /**
     * Get attributes to include in auditing (if empty, all attributes except excluded ones)
     */
    protected function getAuditInclude(): array
    {
        return property_exists($this, 'auditInclude') ? $this->auditInclude : [];
    }

    /**
     * Disable auditing for this model instance
     */
    public function disableAuditing()
    {
        $this->auditingDisabled = true;
        return $this;
    }

    /**
     * Enable auditing for this model instance
     */
    public function enableAuditing()
    {
        $this->auditingDisabled = false;
        return $this;
    }

    /**
     * Check if auditing is disabled for this instance
     */
    public function isAuditingDisabled(): bool
    {
        return property_exists($this, 'auditingDisabled') && $this->auditingDisabled === true;
    }
}