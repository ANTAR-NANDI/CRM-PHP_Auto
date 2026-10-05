<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class EventBudgetItem extends Model { protected $fillable=['event_budget_id','title','quantity','unit_price','amount','note']; }
