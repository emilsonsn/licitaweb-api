<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderProduct extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    public $table = 'tender_products';

    public $fillable = [
        'product_id',
        'quantity',
        'tender_id',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function products()
    {
        return $this->belongsTo(Product::class);
    }
}
