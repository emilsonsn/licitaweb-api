<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotesStatus extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    public $table = 'commitment_notes_status';

    public $fillable = [
        'name',
        'color',
    ];

    public function commitment_notes()
    {
        return $this->hasMany(CommitmentNote::class);
    }
}
