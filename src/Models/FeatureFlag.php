<?php

namespace FilipeFernandes\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    protected $table = 'feature_flags';

    protected $fillable = ['key', 'enabled', 'environments', 'metadata', 'description'];

    protected $casts = [
        'enabled' => 'boolean',
        'environments' => 'array',
        'metadata' => 'array',
    ];

    // Tell Laravel to use "key" column instead of "id" for route model binding
    public function getRouteKeyName()
    {
        return 'key';
    }

    protected static function booted(): void
    {
        static::saved(fn() => Cache::tags('feature_flags')->flush());
        static::deleted(fn() => Cache::tags('feature_flags')->flush());
    }

    public function histories()
    {
        return $this->hasMany(FeatureFlagHistory::class, 'key');
    }

    /**
     * Get Conditions
     *
     * @return Attribute<array>
     */
    protected function conditions(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->metadata['conditions'] ?? [];
        });
    }
}
