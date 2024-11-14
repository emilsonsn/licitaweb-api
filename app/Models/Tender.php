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

    public function items(){
        return $this->hasMany(TenderItem::class);
    }

    public function attachments(){
        return $this->hasMany(TenderAttachment::class);
    }

    public function status()
    {
        return $this->hasOneThrough(
            Status::class,       // Modelo final que você quer retornar
            TenderStatus::class, // Modelo intermediário
            'tender_id',         // Chave estrangeira no modelo TenderStatus
            'id',                // Chave primária no modelo Status
            'id',                // Chave primária no modelo Tender
            'status_id'          // Chave estrangeira no modelo TenderStatus
        );
    }
    
    public function tenderStatus(){
        return $this->hasMany(TenderStatus::class);
    }
}
