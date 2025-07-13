<?php

namespace FilipeFernandes\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    protected $fillable = ['key', 'enabled', 'metadata'];
    protected $casts = ['enabled' => 'boolean', 'metadata' => 'array'];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('feature_flags_all'));
        static::deleted(fn() => Cache::forget('feature_flags_all'));
    }
}
