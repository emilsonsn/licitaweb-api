<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const DELETED_AT = 'deleted_at';

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
}
