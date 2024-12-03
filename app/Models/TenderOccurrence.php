<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderOccurrence extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    public $table = 'tender_occurrences';

    public $fillable = [
        'title',
        'description',
        'user_id',
        'tender_id',
    ];

    public function user(){
        $this->belongsTo(User::class);
    }

    public function tender(){
        $this->belongsTo(Tender::class);
    }

    public function files(){
        $this->hasMany(TenderOccurrenceFile::class);
    }
}
