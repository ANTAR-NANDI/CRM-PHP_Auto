<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TodoType extends Model { protected $fillable = ['name','is_active']; protected function casts(): array { return ['is_active'=>'boolean']; } public function todos(): HasMany { return $this->hasMany(Todo::class); } }
