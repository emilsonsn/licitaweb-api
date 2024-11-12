<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tender extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $table = 'tenders';

    public $fillable = [
        'number',
        'organ',
        'modality_id',
        'contest_date',
        'object',
        'estimated_value',
        'status',
        'items_count',
        'user_id',
    ];

    public function modality(){
        return $this->belongsTo(Modality::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function task(){
        return $this->belongsTo(TenderTask::class);
    }

    public function tenderStatus(){
        return $this->hasMany(TenderStatus::class);
    }
}
