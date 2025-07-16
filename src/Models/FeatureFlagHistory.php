<?php

namespace FilipeFernandes\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlagHistory extends Model
{
    protected $table = 'feature_flags_history';

    // Mass assignable attributes
    protected $fillable = [
        'feature_flag_id',
        'key',
        'enabled',
        'event',
        'metadata',
        'environments',
        'changed_at',
        'changed_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'environments' => 'array',
        'changed_at' => 'datetime',
        'enabled' => 'boolean',
    ];
}
