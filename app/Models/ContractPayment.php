<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractPayment extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    public $table = 'contract_payments';

    public $fillable = [
        'description',
        'contract_id',
        'amount_received',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
