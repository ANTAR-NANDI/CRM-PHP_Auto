<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    protected $fillable = ['name', 'code', 'phone', 'email', 'address', 'contact_person', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
