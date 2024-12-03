<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    public $table = 'notifications';

    public $fillable = [
        'description',
        'message',
        'datetime',
        'user_id',
        'tender_id',
        'status',
    ];

    public function tender(){
        $this->belongsTo(Tender::class);
    }

    public function user_id(){
        $this->belongsTo(User::class);
    }
}
