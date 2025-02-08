<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderItem extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    public $table = 'tender_items';

    public $fillable = [
        'product_id',
        'quantity',
        'unit_value',
        'tender_id',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }
}
