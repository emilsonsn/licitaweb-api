<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitmentNote extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const DELETED_AT = 'deleted_at';

    public $table = 'commitment_notes';

    public $fillable = [
        'contract_id',
        'number',
        'receipt_date',
        'purchase_term',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function products()
    {
        return $this->hasMany(CommitmentNoteProduct::class);
    }
}
