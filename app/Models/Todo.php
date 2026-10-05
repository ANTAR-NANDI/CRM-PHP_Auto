<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Todo extends Model {
    protected $fillable = ['todo_type_id','assigned_to','subject_type','subject_id','task_with','due_at','priority','remind_before_minutes','note','status','completed_at'];
    protected function casts(): array { return ['due_at'=>'datetime','completed_at'=>'datetime']; }
    public function type(): BelongsTo { return $this->belongsTo(TodoType::class, 'todo_type_id'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
}
