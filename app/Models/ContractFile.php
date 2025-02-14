<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractFile extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    public $table = 'contract_files';

    public $fillable = [
        'filename',
        'path',
        'contract_id',
    ];

    public function getPathAttribute($value)
    {
        return $value ? asset('storage/'.$value) : null;
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
