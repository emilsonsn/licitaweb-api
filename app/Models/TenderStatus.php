<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderStatus extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $table = 'tender_status';

    public $fillable = [
        'position',
        'status_id',
        'tender_id',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function tender()
    {
        return $this->hasMany(Tender::class);
    }
}
