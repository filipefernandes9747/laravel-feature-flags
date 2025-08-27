<?php

namespace FilipeFernandes\FeatureFlags\Tests\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'password'];

    /**
     * Is Beta Tester
     *
     * @return Attribute<string>
     */
    protected function is_beta_testers(): Attribute
    {
        return Attribute::make(get: function () {
            return true;
        });
    }
}
