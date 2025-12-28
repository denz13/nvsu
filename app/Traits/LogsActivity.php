<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    /**
     * Boot the trait
     */
    public static function bootLogsActivity()
    {
        // Log when a model is created
        static::created(function ($model) {
            $model->logActivity('created', $model->getAttributes());
        });

        // Log when a model is updated
        static::updated(function ($model) {
            $model->logActivity('updated', $model->getAttributes(), $model->getOriginal());
        });

        // Log when a model is deleted
        static::deleted(function ($model) {
            $model->logActivity('deleted', $model->getAttributes());
        });

        // Log when a model is restored (for SoftDeletes)
        // Check if the model uses SoftDeletes trait
        $usesSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(static::class)
        );
        
        if ($usesSoftDeletes) {
            static::restored(function ($model) {
                $model->logActivity('restored', $model->getAttributes());
            });
        }
    }

    /**
     * Log an activity
     *
     * @param string $action
     * @param array $newValues
     * @param array|null $oldValues
     * @param string|null $description
     * @return ActivityLog
     */
    public function logActivity($action, $newValues = [], $oldValues = null, $description = null)
    {
        // Get current user
        $userId = Auth::id();

        // Get model information
        $modelType = get_class($this);
        $modelId = $this->getKey();

        // Calculate changes for updates
        $changes = null;
        if ($action === 'updated' && $oldValues !== null) {
            $changes = $this->calculateChanges($oldValues, $newValues);
            // Only log if there are actual changes
            if (empty($changes)) {
                return null;
            }
        }

        // Generate description if not provided
        if ($description === null) {
            $description = $this->generateDescription($action, $newValues, $oldValues);
        }

        // Filter sensitive data
        $filteredNewValues = $this->filterSensitiveData($newValues);
        $filteredOldValues = $oldValues ? $this->filterSensitiveData($oldValues) : null;

        // Create activity log
        return ActivityLog::create([
            'user_id' => $userId,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action' => $action,
            'description' => $description,
            'old_values' => $filteredOldValues,
            'new_values' => $filteredNewValues,
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }

    /**
     * Calculate changes between old and new values
     *
     * @param array $oldValues
     * @param array $newValues
     * @return array
     */
    protected function calculateChanges($oldValues, $newValues)
    {
        $changes = [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            
            // Skip timestamps and other auto-updated fields
            if (in_array($key, ['updated_at', 'created_at', 'deleted_at'])) {
                continue;
            }

            // Compare values
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Generate a human-readable description
     *
     * @param string $action
     * @param array $newValues
     * @param array|null $oldValues
     * @return string
     */
    protected function generateDescription($action, $newValues = [], $oldValues = null)
    {
        $modelName = class_basename($this);
        $identifier = $this->getIdentifier($newValues);

        switch ($action) {
            case 'created':
                return "Created {$modelName}: {$identifier}";
            
            case 'updated':
                $changes = $oldValues ? $this->calculateChanges($oldValues, $newValues) : [];
                $changedFields = implode(', ', array_keys($changes));
                return "Updated {$modelName}: {$identifier} ({$changedFields})";
            
            case 'deleted':
                return "Deleted {$modelName}: {$identifier}";
            
            case 'restored':
                return "Restored {$modelName}: {$identifier}";
            
            default:
                return ucfirst($action) . " {$modelName}: {$identifier}";
        }
    }

    /**
     * Get identifier for the model (name, title, etc.)
     *
     * @param array $attributes
     * @return string
     */
    protected function getIdentifier($attributes)
    {
        // Try common identifier fields
        $identifierFields = ['name', 'title', 'student_name', 'organization_name', 'college_name', 'program_name', 'event_name', 'id_number', 'email'];
        
        foreach ($identifierFields as $field) {
            if (isset($attributes[$field])) {
                return $attributes[$field];
            }
        }

        // Fallback to ID
        return 'ID: ' . ($attributes['id'] ?? 'N/A');
    }

    /**
     * Filter sensitive data from being logged
     *
     * @param array $data
     * @return array
     */
    protected function filterSensitiveData($data)
    {
        $sensitiveFields = ['password', 'password_confirmation', 'remember_token', 'api_token', 'secret'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***HIDDEN***';
            }
        }

        return $data;
    }

    /**
     * Get all activity logs for this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'model', 'model_type', 'model_id');
    }

    /**
     * Get the latest activity log for this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function latestActivityLog()
    {
        return $this->morphOne(ActivityLog::class, 'model', 'model_type', 'model_id')->latestOfMany();
    }
}

