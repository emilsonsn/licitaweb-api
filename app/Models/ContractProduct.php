<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractProduct extends Model
{
    use HasFactory;

    public $table = "contract_products";

    protected $fillable = [
        'contract_id',
        'product_id',
        'quantity',
    ];


    public function contract() {
        return $this->belongsTo(Contract::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
