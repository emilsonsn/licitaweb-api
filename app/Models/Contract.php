<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const DELETED_AT = 'deleted_at';

    public $table = 'contracts';

    public $fillable = [
        'contract_number',
        'client_id',
        'tender_id',
        'contract_object',
        'signature_date',
        'start_date',
        'end_date',
        'status',
        'total_contract_value',
        'payment_conditions',
        'outstanding_balance',
        'observations',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function payments()
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function files()
    {
        return $this->hasMany(ContractFile::class);
    }
}
