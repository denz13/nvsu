# Activity Log System

A dynamic activity logging system using Laravel traits that automatically records user actions in the system.

## Features

- ✅ Automatically logs create, update, delete, and restore operations
- ✅ Records user information, IP address, user agent, and URL
- ✅ Tracks old and new values for updates
- ✅ Filters sensitive data (passwords, tokens, etc.)
- ✅ Generates human-readable descriptions
- ✅ Easy to use - just add the trait to any model

## Installation

1. Run the migration:
```bash
php artisan migrate
```

## Usage

### Basic Usage

Simply add the `LogsActivity` trait to any model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class YourModel extends Model
{
    use LogsActivity;
    
    // ... your model code
}
```

### Example Models

#### Organization Model
```php
use App\Traits\LogsActivity;

class organization extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    // ...
}
```

#### Students Model
```php
use App\Traits\LogsActivity;

class students extends Authenticatable
{
    use HasFactory, SoftDeletes, LogsActivity;
    // ...
}
```

## What Gets Logged

The system automatically logs:

1. **Created** - When a new record is created
2. **Updated** - When a record is updated (only logs changed fields)
3. **Deleted** - When a record is deleted
4. **Restored** - When a soft-deleted record is restored

## Activity Log Structure

Each activity log contains:

- `user_id` - ID of the user who performed the action
- `model_type` - Full class name of the model (e.g., `App\Models\students`)
- `model_id` - ID of the affected record
- `action` - Action type (created, updated, deleted, restored)
- `description` - Human-readable description
- `old_values` - Original values (for updates/deletes)
- `new_values` - New values (for creates/updates)
- `changes` - Only changed fields (for updates)
- `ip_address` - IP address of the user
- `user_agent` - Browser/user agent information
- `url` - URL where the action was performed
- `created_at` - Timestamp of the activity

## Querying Activity Logs

### Get all activities for a model instance:
```php
$organization = organization::find(1);
$activities = $organization->activityLogs;
```

### Get latest activity for a model:
```php
$latestActivity = $organization->latestActivityLog;
```

### Query activity logs directly:
```php
use App\Models\ActivityLog;

// Get all activities
$allActivities = ActivityLog::all();

// Filter by action
$createdActivities = ActivityLog::action('created')->get();

// Filter by model type
$studentActivities = ActivityLog::modelType('App\Models\students')->get();

// Filter by user
$userActivities = ActivityLog::forUser(auth()->id())->get();

// Get activities with user relationship
$activities = ActivityLog::with('user')->latest()->get();
```

## Manual Logging

You can also manually log activities:

```php
$organization = organization::find(1);

// Log a custom activity
$organization->logActivity('custom_action', $organization->getAttributes(), null, 'Custom description here');
```

## Sensitive Data Filtering

The system automatically filters sensitive fields:
- `password`
- `password_confirmation`
- `remember_token`
- `api_token`
- `secret`

These fields will be replaced with `***HIDDEN***` in the logs.

## Example Output

When a user creates an organization:

```json
{
  "user_id": 1,
  "model_type": "App\\Models\\organization",
  "model_id": 5,
  "action": "created",
  "description": "Created organization: My Organization",
  "old_values": null,
  "new_values": {
    "organization_name": "My Organization",
    "status": "active"
  },
  "changes": null,
  "ip_address": "127.0.0.1",
  "user_agent": "Mozilla/5.0...",
  "url": "http://nvsu.test/organization/store"
}
```

When a user updates an organization:

```json
{
  "user_id": 1,
  "model_type": "App\\Models\\organization",
  "model_id": 5,
  "action": "updated",
  "description": "Updated organization: My Organization (status, organization_name)",
  "old_values": {
    "organization_name": "My Organization",
    "status": "active"
  },
  "new_values": {
    "organization_name": "Updated Organization",
    "status": "inactive"
  },
  "changes": {
    "organization_name": {
      "old": "My Organization",
      "new": "Updated Organization"
    },
    "status": {
      "old": "active",
      "new": "inactive"
    }
  }
}
```

## Notes

- The trait automatically hooks into Laravel's model events
- No additional code needed in controllers - it works automatically
- Works with SoftDeletes (restore is logged)
- Timestamps (created_at, updated_at, deleted_at) are excluded from change tracking
- Only actual changes are logged for updates

