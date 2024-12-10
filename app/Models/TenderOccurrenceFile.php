<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderOccurrenceFile extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    public $table = 'tender_occurrence_files';

    public $fillable = [
        'filename',
        'path',
        'tender_occurrence_id',
    ];

    public function getPathAttribute($value){
        return $value ? asset('storage/' . $value) : null;
    }

    public function occurrence(){
        $this->belongsTo(TenderOccurrence::class);
    }
}
