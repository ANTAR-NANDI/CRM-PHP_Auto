<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'crm_organizations';
    protected $fillable = ['name', 'phone', 'email', 'location_id', 'contact_person', 'created_by', 'store_id', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }
}
