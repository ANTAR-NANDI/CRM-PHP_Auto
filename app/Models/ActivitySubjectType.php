<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivitySubjectType extends Model
{
    protected $fillable = ['name', 'key', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
