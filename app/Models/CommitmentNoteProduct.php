<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitmentNoteProduct extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const DELETED_AT = 'deleted_at';

    public $table = 'commitment_note_products';

    public $fillable = [
        'commitment_note_id',
        'product_id',
        'quantity',
    ];

    public function commitmentNote()
    {
        return $this->belongsTo(CommitmentNote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
