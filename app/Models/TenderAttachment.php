<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TenderAttachment extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $table = 'tender_attachments';

    public $fillable = [
        'filename',
        'path',
        'user_id',
        'tender_id',
    ];

    public function getAttributePath()
    {
        if(isset($this->path)){
            return Storage::url('task_files/' . $this->path);
        }
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
