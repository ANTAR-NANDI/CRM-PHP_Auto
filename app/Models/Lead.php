<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Lead extends Model {
    protected $fillable = ['name','phone','alternate_phone','email','address','job_title','organization_id','segment_id','product_id','color_id','lead_status_id','pipeline_id','owner_id','activity_type_id','source_id','lead_date','source_detail','remarks','business_card_path','created_by','store_id'];
    protected function casts(): array { return ['lead_date' => 'date']; }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
