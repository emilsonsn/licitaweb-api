<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    public $table = 'products';

    public $fillable = [
        'id',
        'name',
        'sku',
        'category',
        'detailed_description',
        'size',
        'technical_information',
        'brand',
        'origin',
        'model',
        'purchase_cost',
        'freight',
        'taxes_fees',
        'total_cost',
        'profit_margin',
        'sale_price',
        'supplier_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function attachments()
    {
        return $this->hasMany(ProductFile::class);
    }

    public function tender_items()
    {
        return $this->hasMany(TenderItem::class);
    }
}
