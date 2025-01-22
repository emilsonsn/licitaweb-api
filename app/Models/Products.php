<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    public $table = 'products';

    public $fillable = [
        'name',
        'sku',
        'category',
        'detailed_description',
        'tire_size',
        'load_speed_index',
        'brand',
        'origin',
        'model',
        'unit_purchase_cost',
        'unit_freight',
        'taxes_fees',
        'total_unit_cost',
        'profit_margin',
        'unit_sale_price',
        'supplier_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
