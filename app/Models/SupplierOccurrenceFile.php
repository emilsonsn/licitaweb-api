<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierOccurrenceFile extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    public $table = 'supplier_occurrence_files';

    public $fillable = [
        'filename',
        'path',
        'supplier_occurrence_id',
    ];

    public function getPathAttribute($value){
        return $value ? asset('storage/' . $value) : null;
    }

    public function occurrence(){
        return $this->belongsTo(SupplierOccurrence::class);
    }
}