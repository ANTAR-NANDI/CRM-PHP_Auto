<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityType extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function subTypes(): HasMany { return $this->hasMany(ActivitySubType::class); }
    public function activities(): HasMany { return $this->hasMany(Activity::class); }
}
