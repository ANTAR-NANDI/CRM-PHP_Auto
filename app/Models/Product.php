<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'part_no', 'item_category_id', 'segment_id', 'category', 'description', 'technical_specification', 'unit_price', 'commission_type', 'unit_commission',
        'barcode', 'generic_name_id', 'default_supplier_id', 'brand_id', 'unit', 'pieces_per_strip',
        'sell_by_piece', 'sell_by_strip', 'reorder_level', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sell_by_piece' => 'boolean',
            'sell_by_strip' => 'boolean',
            'unit_price' => 'decimal:2',
            'unit_commission' => 'decimal:2',
        ];
    }

    public function genericName(): BelongsTo
    {
        return $this->belongsTo(GenericName::class);
    }

    public function defaultSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function openingStocks(): HasMany
    {
        return $this->hasMany(ProductOpeningStock::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
