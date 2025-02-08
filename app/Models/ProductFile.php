<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFile extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    public $table = 'product_files';

    public $fillable = [
        'filename',
        'path',
        'product_id',
    ];

    public function getPathAttribute($value)
    {
        return $value ? asset('storage/'.$value) : null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
